# bifrost/queue-worker

Worker opcional para consumir tarefas publicadas em qualquer implementacao do
contrato `Bifrost\Framework\Contracts\Queue`.

```php
$payload = new TaskPayload('emails.send', ['email' => 'user@example.com']);
$queue->push('default', $payload->toArray());

$registry = (new TaskRegistry())->add('emails.send', new SendEmailTaskHandler());
$worker = new QueueWorker($queue, $registry);
$worker->workOnce('default');
```

Use `TaskPayload::fromArray()` para reconstruir payloads serializados pela fila.
O worker reencaminha tarefas com falha enquanto `maxAttempts` nao for atingido.
