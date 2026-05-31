# bifrost/log-stdout

Pacote opcional de logs em `stdout` ou `stderr` para o Bifrost Framework.
E indicado para Docker, Kubernetes e plataformas que coletam logs do processo.

```php
use Bifrost\Extension\LogStdout\StdoutLogExtension;
use Bifrost\Framework\Logging\Logger;

$application->extend(new StdoutLogExtension([
    'stream' => 'stdout',
]));

$application->container()
    ->get(Logger::class)
    ->info('Requisicao recebida', ['route' => '/health']);
```

Cada entrada e gravada como uma linha JSON com `timestamp`, `level`,
`message`, `request_id` e `context`.
