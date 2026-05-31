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

    public static function idle(): self
    {
        return new self(self::IDLE);
    }

    public static function processed(TaskPayload $payload): self
    {
        return new self(self::PROCESSED, $payload);
    }

    public static function retried(TaskPayload $payload, Throwable $error): self
    {
        return new self(self::RETRIED, $payload, $error);
    }

    public static function failed(TaskPayload $payload, Throwable $error): self
    {
        return new self(self::FAILED, $payload, $error);
    }

    public function isIdle(): bool
    {
        return $this->status === self::IDLE;
    }

    public function isProcessed(): bool
    {
        return $this->status === self::PROCESSED;
    }

    public function isRetried(): bool
    {
        return $this->status === self::RETRIED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::FAILED;
    }

    public function payload(): ?TaskPayload
    {
        return $this->payload;
    }

    public function error(): ?Throwable
    {
        return $this->error;
    }
}
