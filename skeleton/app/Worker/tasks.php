<?php

declare(strict_types=1);

use App\Worker\ExampleTaskHandler;
use Bifrost\Extension\QueueWorker\TaskRegistry;

return (new TaskRegistry())
    ->add('example.ping', new ExampleTaskHandler());
