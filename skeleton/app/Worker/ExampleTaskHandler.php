<?php

declare(strict_types=1);

namespace App\Worker;

use Bifrost\Extension\QueueWorker\TaskHandler;
use Bifrost\Extension\QueueWorker\TaskPayload;

final class ExampleTaskHandler implements TaskHandler
{
    /**
     * Processa uma tarefa de exemplo emitindo uma linha no stdout.
     *
     * Cada handler deve ter uma responsabilidade clara. Registre novos
     * handlers em tasks.php para que o worker consiga localiza-los pelo nome.
     */
    public function handle(TaskPayload $payload): void
    {
        fwrite(STDOUT, sprintf("Tarefa %s processada.\n", $payload->task()));
    }
}
