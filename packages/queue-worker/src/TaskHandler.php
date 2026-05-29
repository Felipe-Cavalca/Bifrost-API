<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueWorker;

interface TaskHandler
{
    /**
     * Processa uma tarefa recebida do worker.
     */
    public function handle(TaskPayload $payload): void;
}
