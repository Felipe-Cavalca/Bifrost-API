<?php

declare(strict_types=1);

namespace Bifrost\Framework\Logging;

use Bifrost\Framework\Contracts\LogWriter;
use Closure;
use Throwable;

/**
 * Logger estruturado que depende apenas de um LogWriter.
 *
 * O framework nao escolhe destino de log. Pacotes opcionais registram o writer
 * concreto e podem expor esta classe no container.
 */
final class Logger
{
    /** @var Closure(): string */
    private readonly Closure $timestampResolver;

    /** @var Closure(): string */
    private readonly Closure $requestIdResolver;

    public function __construct(
        private readonly LogWriter $writer,
        ?callable $timestampResolver = null,
        ?callable $requestIdResolver = null
    ) {
        $this->timestampResolver = Closure::fromCallable($timestampResolver ?? static fn (): string => gmdate('c'));
        $this->requestIdResolver = Closure::fromCallable($requestIdResolver ?? self::defaultRequestIdResolver());
    }

    /**
     * @param array<string, mixed> $context Contexto seguro para persistir no log.
     */
    public function debug(string $message, array $context = []): void
    {
        $this->write('debug', $message, $context);
    }

    /**
     * @param array<string, mixed> $context Contexto seguro para persistir no log.
     */
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    /**
     * @param array<string, mixed> $context Contexto seguro para persistir no log.
     */
    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    /**
     * @param array<string, mixed> $context Contexto seguro para persistir no log.
     */
    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    /**
     * Registra uma excecao sem copiar a mensagem automaticamente para evitar vazamento de dados sensiveis.
     *
     * @param array<string, mixed> $context Contexto seguro para persistir no log.
     */
    public function exception(Throwable $exception, array $context = []): void
    {
        $this->error('Unhandled exception', array_merge($context, [
            'exception' => [
                'class' => $exception::class,
                'code' => $exception->getCode(),
            ],
        ]));
    }

    /**
     * @param array<string, mixed> $context Contexto seguro para persistir no log.
     */
    public function write(string $level, string $message, array $context = []): void
    {
        $this->writer->write([
            'timestamp' => ($this->timestampResolver)(),
            'level' => $level,
            'message' => $message,
            'request_id' => ($this->requestIdResolver)(),
            'context' => $context,
        ]);
    }

    /**
     * @return Closure(): string
     */
    private static function defaultRequestIdResolver(): Closure
    {
        $requestId = null;

        return static function () use (&$requestId): string {
            if ($requestId !== null) {
                return $requestId;
            }

            $header = $_SERVER['HTTP_X_REQUEST_ID'] ?? null;
            if (is_string($header) && trim($header) !== '') {
                return $requestId = trim($header);
            }

            return $requestId = bin2hex(random_bytes(16));
        };
    }
}
