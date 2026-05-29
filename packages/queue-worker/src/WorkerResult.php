<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueWorker;

use Throwable;

final class WorkerResult
{
    private const IDLE = 'idle';
    private const PROCESSED = 'processed';
    private const RETRIED = 'retried';
    private const FAILED = 'failed';

    private function __construct(
        private readonly string $status,
        private readonly ?TaskPayload $payload = null,
        private readonly ?Throwable $error = null
    ) {
    }

    /**
     * Cria resultado para fila vazia.
     */
    public static function idle(): self
    {
        return new self(self::IDLE);
    }

    /**
     * Cria resultado para tarefa processada com sucesso.
     */
    public static function processed(TaskPayload $payload): self
    {
        return new self(self::PROCESSED, $payload);
    }

    /**
     * Cria resultado para tarefa reenfileirada apos falha.
     */
    public static function retried(TaskPayload $payload, Throwable $error): self
    {
        return new self(self::RETRIED, $payload, $error);
    }

    /**
     * Cria resultado para falha definitiva.
     */
    public static function failed(TaskPayload $payload, Throwable $error): self
    {
        return new self(self::FAILED, $payload, $error);
    }

    /**
     * Indica que nenhuma tarefa foi encontrada.
     */
    public function isIdle(): bool
    {
        return $this->status === self::IDLE;
    }

    /**
     * Indica que a tarefa foi processada.
     */
    public function isProcessed(): bool
    {
        return $this->status === self::PROCESSED;
    }

    /**
     * Indica que a tarefa foi reenfileirada.
     */
    public function isRetried(): bool
    {
        return $this->status === self::RETRIED;
    }

    /**
     * Indica que a tarefa falhou definitivamente.
     */
    public function isFailed(): bool
    {
        return $this->status === self::FAILED;
    }

    /**
     * Retorna o payload relacionado ao resultado, quando existir.
     */
    public function payload(): ?TaskPayload
    {
        return $this->payload;
    }

    /**
     * Retorna o erro relacionado ao resultado, quando existir.
     */
    public function error(): ?Throwable
    {
        return $this->error;
    }
}
