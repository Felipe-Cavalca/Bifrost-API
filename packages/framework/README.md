# Bifrost Framework

Pacote HTTP desacoplado do Bifrost. Ele fornece aplicação, roteamento, middleware,
request/response e contratos para extensões opcionais.

```bash
composer require bifrost/framework
```

```php
<?php

use Bifrost\Framework\Application;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;

$app = Application::create();
$app->get('/health', fn (): Response => Response::json(['status' => 'healthy']));
$app->emit($app->handle(Request::fromGlobals()));
```

O pacote não inclui Redis, banco de dados, fila, storage ou controllers de uma
aplicação real. Esses recursos devem ser adicionados por extensões.
