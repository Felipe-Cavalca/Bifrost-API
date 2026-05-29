<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueWorker;

use InvalidArgumentException;
use JsonSerializable;

final class TaskPayload implements JsonSerializable
{
    /**
     * @param string $task Nome pesquisavel da tarefa.
     * @param array<string, mixed> $data Dados serializaveis da tarefa.
     * @param int $attempts Quantidade de tentativas ja registradas.
     * @param int $maxAttempts Limite maximo de tentativas.
     */
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

    /**
     * Cria um payload validado a partir dos dados vindos da fila.
     *
     * @param array<string, mixed> $payload
     */
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

    /**
     * Retorna o nome da tarefa.
     */
    public function task(): string
    {
        return $this->task;
    }

    /**
     * Retorna os dados serializaveis da tarefa.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Retorna a quantidade de tentativas ja feitas.
     */
    public function attempts(): int
    {
        return $this->attempts;
    }

    /**
     * Retorna o limite maximo de tentativas.
     */
    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * Retorna uma nova instancia com uma falha registrada.
     */
    public function withRecordedFailure(): self
    {
        return new self(
            task: $this->task,
            data: $this->data,
            attempts: $this->attempts + 1,
            maxAttempts: $this->maxAttempts
        );
    }

    /**
     * Indica se a tarefa ainda pode ser reenfileirada.
     */
    public function canRetry(): bool
    {
        return $this->attempts < $this->maxAttempts;
    }

    /**
     * Serializa o payload para gravacao na fila.
     *
     * @return array{task: string, data: array<string, mixed>, attempts: int, max_attempts: int}
     */
    public function toArray(): array
    {
        return [
            'task' => $this->task,
            'data' => $this->data,
            'attempts' => $this->attempts,
            'max_attempts' => $this->maxAttempts,
        ];
    }

    /**
     * @return array{task: string, data: array<string, mixed>, attempts: int, max_attempts: int}
     */
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
