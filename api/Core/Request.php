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
    private Get $Get;

    public function __construct()
    {
        $this->Get = new Get();
        Settings::init();
    }

    public function __toString(): string
    {
        return $this->handleResponse($this->run($this->Get->controller, $this->Get->action));
    }

    /**
     * Executa uma ação de um Controller.
     * @param string|Controller $controller Nome do Controller.
     * @param string $action Nome da ação do Controller.
     * @return mixed Retorna o resultado da ação do Controller.
     */
    public static function run(string|Controller $controller, string $action): Responseable
    {
        try {
            if (is_string($controller) && (empty($controller) || !self::validateControllerName($controller))) {
                throw new AppError(HttpResponse::notFound(["controller" => $controller], "Controller not found"));
            }

            $objController = self::resolveController($controller);

            if (!self::validateActionName($objController, $action)) {
                throw new AppError(HttpResponse::notFound(["action" => $action], "Action not found"));
            }

            $reflectionMethod = new ReflectionMethod($objController, $action);
            $attributes = self::getAttributes($reflectionMethod);
            $return = self::runBeforeAttributes($attributes);

            // Se o método beforeRun retornar algo, não executa a ação do controller.
            if ($return !== null) {
                return $return;
            }

            $return = self::runAction($objController, $action);
            self::runAfterAttributes($attributes, $return);
            return $return;
        } catch (\Throwable $erro) {
            if ($erro instanceof AppError) {
                return $erro->response;
            }
            return HttpResponse::internalServerError([], $erro->getMessage());
        }
    }

    /**
     * Valida o nome do Controller.
     * @param string $controller Nome do Controller.
     * @return bool Retorna true se o Controller for válido, caso contrário, false.
     */
    private static function validateControllerName(string $controller): bool
    {
        $cacheKey = self::getCacheKey('controller_exists', $controller);
        $cached = Apcu::fetch($cacheKey);
        if (is_bool($cached)) {
            return $cached;
        }

        $nameController = Path::FOLDER->toDirectory() . Path::CONTROLLERS->toDirectory() . $controller . ".php";
        $controllerClass = Path::NAMESPACE->value . Path::CONTROLLERS->value . $controller;
        $exists = is_readable($nameController) && class_exists($controllerClass);

        Apcu::store($cacheKey, $exists);
        return $exists;
    }

    /**
     * Carrega o Controller.
     * @param string $controllerName Nome do Controller.
     * @return Controller Retorna uma instância do Controller.
     */
    private static function loadController(string $controllerName): Controller
    {
        $controller = Path::NAMESPACE->value . Path::CONTROLLERS->value . $controllerName;
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
        $cacheKey = self::getCacheKey('action_exists', $controller::class, $action);
        $cached = Apcu::fetch($cacheKey);
        if (is_bool($cached)) {
            return $cached;
        }

        $exists = method_exists($controller, $action);
        Apcu::store($cacheKey, $exists);
        return $exists;
    }

    /**
     * Obtém os atributos de um método.
     * @param ReflectionMethod $reflectionMethod Método a ser analisado.
     * @return array Retorna um array com os atributos do método.
     */
    private static function getAttributes(ReflectionMethod $reflectionMethod): array
    {
        $cacheKey = self::getCacheKey(
            'method_attributes',
            $reflectionMethod->getDeclaringClass()->getName(),
            $reflectionMethod->getName()
        );
        $cached = Apcu::fetch($cacheKey);
        if (is_array($cached)) {
            return self::hydrateAttributes($cached);
        }

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
     * @return void
     */
    private static function runAfterAttributes(array $attributes, Responseable $return): void
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof AttributeAfter) {
                $attribute->after($return);
            }
        }
    }

    /**
     * Executa a ação do Controller.
     * @param Controller $controller Instância do Controller.
     * @param string $action Nome da ação do Controller.
     * @return Responseable Retorna o resultado da ação executada pelo Controller.
     */
    private static function runAction(Controller $controller, string $action): Responseable
    {
        return call_user_func([$controller, $action]);
    }

    /**
     * Lida com a resposta retornada pela ação do Controller.
     * @param mixed $return A resposta retornada pela ação do Controller.
     * @return string Retorna a resposta processada como string.
     */
    private static function handleResponse(mixed $return): string
    {
        if (is_array($return) || $return instanceof Responseable) {
            return json_encode($return);
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
        $attributes = self::getAttributes(new ReflectionMethod($controller, $action));
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

}
