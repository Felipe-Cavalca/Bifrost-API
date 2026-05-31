<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueWorker;

final class QueueWorkerCommand
{
    /**
     * @param QueueWorker $worker Worker responsavel pelo processamento.
     * @param int $defaultSleepSeconds Pausa padrao quando a fila estiver vazia.
     */
    public function __construct(
        private readonly QueueWorker $worker,
        private readonly int $defaultSleepSeconds = 1
    ) {
    }

    /**
     * Executa o loop de consumo da fila.
     *
     * @param list<string> $argv Argumentos de linha de comando.
     * @return int Codigo de saida do processo.
     */
    public function run(array $argv): int
    {
        $options = $this->options($argv);
        $queue = $options['queue'];
        $sleepSeconds = $options['sleep'];
        $maxJobs = $options['max_jobs'];
        $processedJobs = 0;

        while (true) {
            $result = $this->worker->workOnce($queue);

            if ($result->isFailed()) {
                fwrite(STDERR, sprintf("Tarefa falhou definitivamente na fila %s.\n", $queue));

                return 1;
            }

            if ($result->isProcessed() || $result->isRetried()) {
                $processedJobs++;
            }

            if ($maxJobs > 0 && $processedJobs >= $maxJobs) {
                return 0;
            }

            if ($result->isIdle()) {
                sleep($sleepSeconds);
            }
        }
    }

    /**
     * @return array{queue: string, sleep: int, max_jobs: int}
     */
    private function options(array $argv): array
    {
        $options = [
            'queue' => getenv('QUEUE_NAME') ?: 'default',
            'sleep' => $this->defaultSleepSeconds,
            'max_jobs' => 0,
        ];

        foreach (array_slice($argv, 1) as $argument) {
            if (str_starts_with($argument, '--queue=')) {
                $options['queue'] = substr($argument, 8);
                continue;
            }

            if (str_starts_with($argument, '--sleep=')) {
                $options['sleep'] = max(0, (int) substr($argument, 8));
                continue;
            }

            if (str_starts_with($argument, '--max-jobs=')) {
                $options['max_jobs'] = max(0, (int) substr($argument, 11));
            }
        }

        return $options;
    }
}
