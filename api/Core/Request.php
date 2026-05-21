<?php

/**
 * It is responsible for initializing the configuration and managing the system's lifecycle.
 *
 * @category Core
 * @copyright 2024
 */

namespace Bifrost\Core;

use Bifrost\Class\HttpResponse;
use Bifrost\Core\AppError;
use Bifrost\Core\Get;
use Bifrost\Core\Settings;
use Bifrost\Enum\Path;
use Bifrost\Integration\Apcu;
use Bifrost\Interface\Attribute;
use Bifrost\Interface\AttributeAfter;
use Bifrost\Interface\AttributeBefore;
use Bifrost\Interface\Controller;
use Bifrost\Interface\Responseable;
use ReflectionMethod;

/**
 * Class Request
 *
 * This is the main class of the Bifrost system.
 * It is responsible for initializing the configuration and managing the system's lifecycle.
 *
 * @package Bifrost\Core
 * @author Felipe dos S. Cavalca
 */
final class Request
{
    private const JSON_RESPONSE_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    private Get $Get;
    private string $controller;
    private string $action;
    private bool $routeMapped;
    private static array $controllerExistsCache = [];
    private static array $actionExistsCache = [];
    private static array $attributesMetadataCache = [];

    public function __construct()
    {
        $this->Get = new Get();
        $this->controller = Get::$controller;
        $this->action = Get::$action;
        $this->routeMapped = Get::$routeMapped;
        Settings::init();
        Logger::sendRequestIdHeader();
    }

    public function __toString(): string
    {
        return $this->handleResponse($this->run($this->controller, $this->action, $this->routeMapped));
    }

    /**
     * Executa uma ação de um Controller.
     * @param string|Controller $controller Nome do Controller.
     * @param string $action Nome da ação do Controller.
     * @return mixed Retorna o resultado da ação do Controller.
     */
    public static function run(string|Controller $controller, string $action, bool $trustedRoute = false): Responseable
    {
        $attributes = [];
        $response = null;
        $loggerEnabled = Logger::isEnabled();

        if ($loggerEnabled) {
            Logger::info('Request started', [
                'controller' => is_string($controller) ? $controller : $controller::class,
                'action' => $action,
                'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'path' => parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: null,
            ]);
        }

        try {
            if (!$trustedRoute && is_string($controller) && (empty($controller) || !self::validateControllerName($controller))) {
                throw new AppError(HttpResponse::notFound(["controller" => $controller], "Controller not found"));
            }

            $objController = self::resolveController($controller);

            if (!$trustedRoute && !self::validateActionName($objController, $action)) {
                throw new AppError(HttpResponse::notFound(["action" => $action], "Action not found"));
            }

            $attributes = self::getAttributes($objController, $action);
            $return = self::runBeforeAttributes($attributes);

            // Se o método beforeRun retornar algo, não executa a ação do controller.
            if ($return !== null) {
                $response = $return;
            } else {
                $response = self::runAction($objController, $action);
                if ($loggerEnabled) {
                    Logger::info('Request finished', [
                        'controller' => $objController::class,
                        'action' => $action,
                    ]);
                }
            }
        } catch (\Throwable $erro) {
            if ($erro instanceof AppError) {
                Logger::error('Handled application error', [
                    'status' => $erro->response->status->value,
                    'message' => $erro->response->message,
                ]);
                $response = $erro->response;
            } else {
                Logger::exception($erro, [
                    'controller' => is_string($controller) ? $controller : $controller::class,
                    'action' => $action,
                ]);
                $response = HttpResponse::internalServerError([], $erro->getMessage());
            }
        }

        $afterAttributeError = self::runAfterAttributes($attributes, $response);
        if ($afterAttributeError !== null) {
            Logger::exception($afterAttributeError);
            $response = HttpResponse::internalServerError([], 'After attribute failed');
        }

        if ($response instanceof HttpResponse && $loggerEnabled) {
            $response->addRequestId(Logger::requestId());
        }

        return $response;
    }

    /**
     * Valida o nome do Controller.
     * @param string $controller Nome do Controller.
     * @return bool Retorna true se o Controller for válido, caso contrário, false.
     */
    private static function validateControllerName(string $controller): bool
    {
        if (array_key_exists($controller, self::$controllerExistsCache)) {
            return self::$controllerExistsCache[$controller];
        }

        $cacheKey = self::getCacheKey('controller_exists', $controller);
        $cached = Apcu::fetch($cacheKey);
        if (is_bool($cached)) {
            return self::$controllerExistsCache[$controller] = $cached;
        }

        $nameController = Path::FOLDER->toDirectory() . Path::CONTROLLERS->toDirectory() . $controller . ".php";
        $controllerClass = self::controllerClass($controller);
        $exists = is_readable($nameController) && class_exists($controllerClass);

        Apcu::store($cacheKey, $exists);
        return self::$controllerExistsCache[$controller] = $exists;
    }

    /**
     * Carrega o Controller.
     * @param string $controllerName Nome do Controller.
     * @return Controller Retorna uma instância do Controller.
     */
    private static function loadController(string $controllerName): Controller
    {
        $controller = self::controllerClass($controllerName);
        return new $controller();
    }

    /**
     * Valida o nome da ação do Controller.
     * @param Controller $controller Instância do Controller.
     * @param string $action Nome da ação do Controller.
     * @return bool Retorna true se a ação for válida, caso contrário, false.
     */
    private static function validateActionName(Controller $controller, string $action): bool
    {
        $controllerClass = $controller::class;
        $localCacheKey = $controllerClass . '::' . $action;
        if (array_key_exists($localCacheKey, self::$actionExistsCache)) {
            return self::$actionExistsCache[$localCacheKey];
        }

        $cacheKey = self::getCacheKey('action_exists', $controllerClass, $action);
        $cached = Apcu::fetch($cacheKey);
        if (is_bool($cached)) {
            return self::$actionExistsCache[$localCacheKey] = $cached;
        }

        $exists = method_exists($controller, $action);
        Apcu::store($cacheKey, $exists);
        return self::$actionExistsCache[$localCacheKey] = $exists;
    }

    /**
     * Obtém os atributos de um método.
     * @param ReflectionMethod $reflectionMethod Método a ser analisado.
     * @return array Retorna um array com os atributos do método.
     */
    private static function getAttributes(Controller $controller, string $action): array
    {
        $controllerClass = $controller::class;
        $cacheKey = self::getCacheKey(
            'method_attributes',
            $controllerClass,
            $action
        );

        if (array_key_exists($cacheKey, self::$attributesMetadataCache)) {
            return self::hydrateAttributes(self::$attributesMetadataCache[$cacheKey]);
        }

        $cached = Apcu::fetch($cacheKey);
        if (is_array($cached)) {
            self::$attributesMetadataCache[$cacheKey] = $cached;
            return self::hydrateAttributes($cached);
        }

        $reflectionMethod = new ReflectionMethod($controller, $action);
        $attributesReturn = [];
        $attributesMetadata = [];
        $attributes = $reflectionMethod->getAttributes();
        foreach ($attributes as $attribute) {
            $attributesMetadata[] = [
                'name' => $attribute->getName(),
                'arguments' => $attribute->getArguments(),
            ];
            $attributesReturn[] = $attribute->newInstance();
        }

        self::$attributesMetadataCache[$cacheKey] = $attributesMetadata;
        Apcu::store($cacheKey, $attributesMetadata);
        return $attributesReturn;
    }

    /**
     * Executa os métodos before dos atributos.
     * @param array $attributes Atributos do método.
     * @return null|Responseable Retorna o resultado do método before, se houver.
     */
    private static function runBeforeAttributes(array $attributes): null|Responseable
    {
        if ($attributes === []) {
            return null;
        }

        foreach ($attributes as $attribute) {
            if ($attribute instanceof AttributeBefore) {
                $retorno = $attribute->before();
                if ($retorno !== null) {
                    return $retorno;
                }
            }
        }
        return null;
    }

    /**
     * Executa os métodos after dos atributos.
     * @param array $attributes Atributos do método.
     * @param Responseable $return Retorno da ação do Controller.
     * @return null|\Throwable Retorna a falha do atributo after, se houver.
     */
    private static function runAfterAttributes(array $attributes, Responseable $return): null|\Throwable
    {
        if ($attributes === []) {
            return null;
        }

        foreach ($attributes as $attribute) {
            if ($attribute instanceof AttributeAfter) {
                try {
                    $attribute->after($return);
                } catch (\Throwable $exception) {
                    return $exception;
                }
            }
        }
        return null;
    }

    /**
     * Executa a ação do Controller.
     * @param Controller $controller Instância do Controller.
     * @param string $action Nome da ação do Controller.
     * @return Responseable Retorna o resultado da ação executada pelo Controller.
     */
    private static function runAction(Controller $controller, string $action): Responseable
    {
        return $controller->{$action}();
    }

    /**
     * Lida com a resposta retornada pela ação do Controller.
     * @param mixed $return A resposta retornada pela ação do Controller.
     * @return string Retorna a resposta processada como string.
     */
    private static function handleResponse(mixed $return): string
    {
        if (is_array($return) || $return instanceof Responseable) {
            return json_encode($return, self::JSON_RESPONSE_OPTIONS);
        } else {
            return (string) $return;
        }
    }

    /**
     * Obtém os atributos de opções de um Controller e ação.
     * @param string|Controller $controller Nome do Controller ou instância do Controller.
     * @param string $action Nome da ação do Controller.
     * @return array Retorna um array com as opções dos atributos.
     */
    public static function getOptionsAttributes(string|Controller $controller, string $action): array
    {
        $controller = self::resolveController($controller);
        $attributes = self::getAttributes($controller, $action);
        $options = [];
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Attribute) {
                $options = array_merge($options, $attribute->getOptions());
            }
        }
        return $options;
    }

    private static function resolveController(string|Controller $controller): Controller
    {
        if ($controller instanceof Controller) {
            return $controller;
        }

        return self::loadController($controller);
    }

    private static function hydrateAttributes(array $attributesMetadata): array
    {
        $attributes = [];
        foreach ($attributesMetadata as $attributeMetadata) {
            $className = $attributeMetadata['name'];
            $arguments = $attributeMetadata['arguments'];
            $attributes[] = new $className(...$arguments);
        }

        return $attributes;
    }

    private static function getCacheKey(string ...$parts): string
    {
        return Cache::buildKey('request', ...$parts);
    }

    private static function controllerClass(string $controller): string
    {
        return Path::NAMESPACE->value . Path::CONTROLLERS->value . ucfirst($controller);
    }
}
