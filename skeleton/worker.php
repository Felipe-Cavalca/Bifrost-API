<?php

declare(strict_types=1);

use Bifrost\Extension\QueueWorker\QueueWorker;
use Bifrost\Extension\QueueWorker\QueueWorkerCommand;
use Bifrost\Extension\QueueWorker\TaskRegistry;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\Queue;

require __DIR__ . '/vendor/autoload.php';

if (!class_exists(QueueWorker::class)) {
    throw new RuntimeException('Instale bifrost/queue-worker para executar o worker de filas.');
}

/** @var Application $app */
$app = require __DIR__ . '/bootstrap/app.php';

/** @var TaskRegistry $tasks */
$tasks = require __DIR__ . '/app/Worker/tasks.php';

$command = new QueueWorkerCommand(new QueueWorker(
    queue: $app->container()->get(Queue::class),
    tasks: $tasks
));

exit($command->run($argv));
