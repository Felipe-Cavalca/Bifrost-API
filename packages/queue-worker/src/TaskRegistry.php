<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueWorker;

use RuntimeException;

final class TaskRegistry
{
    /** @var array<string, TaskHandler|callable> */
    private array $handlers = [];

    /**
     * Registra um handler para um nome de tarefa.
     *
     * @param string $task Nome pesquisavel da tarefa.
     * @param TaskHandler|callable $handler Handler invocavel pelo worker.
     */
    public function add(string $task, TaskHandler|callable $handler): self
    {
        if ($task === '') {
            throw new RuntimeException('O nome da tarefa nao pode ser vazio.');
        }

        $this->handlers[$task] = $handler;

        return $this;
    }

    /**
     * Executa o handler registrado para o payload informado.
     */
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
