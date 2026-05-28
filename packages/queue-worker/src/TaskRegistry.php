<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueWorker;

use RuntimeException;

final class TaskRegistry
{
    /** @var array<string, TaskHandler|callable> */
    private array $handlers = [];

    public function add(string $task, TaskHandler|callable $handler): self
    {
        if ($task === '') {
            throw new RuntimeException('O nome da tarefa nao pode ser vazio.');
        }

        $this->handlers[$task] = $handler;

        return $this;
    }

    public function handle(TaskPayload $payload): void
    {
        $handler = $this->handlers[$payload->task()] ?? null;

        if ($handler === null) {
            throw new RuntimeException(sprintf('Nenhum handler registrado para a tarefa %s.', $payload->task()));
        }

        if ($handler instanceof TaskHandler) {
            $handler->handle($payload);
            return;
        }

        $handler($payload);
    }
}
