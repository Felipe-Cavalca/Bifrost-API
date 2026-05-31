<?php

declare(strict_types=1);

use App\Worker\ExampleTaskHandler;
use Bifrost\Extension\QueueWorker\TaskRegistry;

// Este registro conecta o nome gravado na fila ao handler que executa a tarefa.
// Prefira nomes pesquisaveis e estaveis, como documents.process ou email.send.
return (new TaskRegistry())
    ->add('example.ping', new ExampleTaskHandler());
