<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueWorker;

use InvalidArgumentException;
use JsonSerializable;

final class TaskPayload implements JsonSerializable
{
    public function __construct(
        private readonly string $task,
        private readonly array $data = [],
        private readonly int $attempts = 0,
        private readonly int $maxAttempts = 3
    ) {
        if ($this->task === '') {
            throw new InvalidArgumentException('O nome da tarefa nao pode ser vazio.');
        }

        if ($this->attempts < 0) {
            throw new InvalidArgumentException('A quantidade de tentativas nao pode ser negativa.');
        }

        if ($this->maxAttempts < 1) {
            throw new InvalidArgumentException('A quantidade maxima de tentativas deve ser maior que zero.');
        }

        self::assertSerializableArray($this->data);
    }

    public static function fromArray(array $payload): self
    {
        $task = $payload['task'] ?? null;
        if (!is_string($task)) {
            throw new InvalidArgumentException('Payload de tarefa deve conter task como string.');
        }

        $data = $payload['data'] ?? [];
        if (!is_array($data)) {
            throw new InvalidArgumentException('Payload de tarefa deve conter data como array.');
        }

        return new self(
            task: $task,
            data: $data,
            attempts: self::integerValue($payload['attempts'] ?? 0, 'attempts'),
            maxAttempts: self::integerValue($payload['max_attempts'] ?? 3, 'max_attempts')
        );
    }

    public function task(): string
    {
        return $this->task;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function attempts(): int
    {
        return $this->attempts;
    }

    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function withRecordedFailure(): self
    {
        return new self(
            task: $this->task,
            data: $this->data,
            attempts: $this->attempts + 1,
            maxAttempts: $this->maxAttempts
        );
    }

    public function canRetry(): bool
    {
        return $this->attempts < $this->maxAttempts;
    }

    public function toArray(): array
    {
        return [
            'task' => $this->task,
            'data' => $this->data,
            'attempts' => $this->attempts,
            'max_attempts' => $this->maxAttempts,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private static function integerValue(mixed $value, string $field): int
    {
        if (!is_int($value)) {
            throw new InvalidArgumentException(sprintf('Payload de tarefa deve conter %s como inteiro.', $field));
        }

        return $value;
    }

    private static function assertSerializableArray(array $value): void
    {
        foreach ($value as $item) {
            if (is_array($item)) {
                self::assertSerializableArray($item);
                continue;
            }

            if ($item === null || is_bool($item) || is_int($item) || is_float($item) || is_string($item)) {
                continue;
            }

            throw new InvalidArgumentException('Payload de tarefa aceita apenas valores escalares, nulos e arrays.');
        }
    }
}
