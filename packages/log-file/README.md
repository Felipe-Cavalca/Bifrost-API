# bifrost/log-file

Pacote opcional de logs em arquivo para o Bifrost Framework.

```php
use Bifrost\Extension\LogFile\FileLogExtension;
use Bifrost\Framework\Logging\Logger;

$application->extend(new FileLogExtension([
    'path' => __DIR__ . '/../storage/logs/app.log',
]));

$application->container()
    ->get(Logger::class)
    ->info('Requisicao recebida', ['route' => '/health']);
```

Cada entrada e gravada como uma linha JSON com o formato:

```php
[
    'timestamp' => gmdate('c'),
    'level' => 'info',
    'message' => 'Requisicao recebida',
    'request_id' => '...',
    'context' => [],
]
```

O pacote implementa `Bifrost\Framework\Contracts\LogWriter` e deve ser
instalado somente quando a aplicacao precisar gravar logs em arquivo.
