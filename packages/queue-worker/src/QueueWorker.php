<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueWorker;

use Bifrost\Framework\Contracts\Queue;
use Throwable;

final class QueueWorker
{
    /**
     * @param Queue $queue Fila usada para buscar e reenfileirar tarefas.
     * @param TaskRegistry $tasks Registro de handlers disponiveis.
     */
    public function __construct(
        private readonly Queue $queue,
        private readonly TaskRegistry $tasks
    ) {
    }

    /**
     * Processa uma unica mensagem da fila.
     *
     * @param string $queue Nome da fila consumida.
     */
    public function workOnce(string $queue): WorkerResult
    {
        $payload = $this->queue->pop($queue);

        if ($payload === null) {
            return WorkerResult::idle();
        }

        $taskPayload = TaskPayload::fromArray($payload);

        try {
            $this->tasks->handle($taskPayload);

            return WorkerResult::processed($taskPayload);
        } catch (Throwable $error) {
            $failedPayload = $taskPayload->withRecordedFailure();

            if ($failedPayload->canRetry()) {
                $this->queue->push($queue, $failedPayload->toArray());

                return WorkerResult::retried($failedPayload, $error);
            }

            return WorkerResult::failed($failedPayload, $error);
        }
    }
}
