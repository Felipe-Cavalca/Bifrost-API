# bifrost/redis

Cliente Redis opcional e reutilizavel para extensoes do Bifrost Framework.

Use este pacote quando uma extensao precisar de Redis sem duplicar a logica de
conexao. `RedisConnectionManager` reutiliza a mesma conexao para configuracoes
iguais e permite trocar a factory por uma implementacao futura de cluster.

```php
use Bifrost\Extension\Redis\RedisConfig;
use Bifrost\Extension\Redis\RedisExtension;

$application->extend(new RedisExtension());

$redis = $application->container()
    ->get(Bifrost\Extension\Redis\Contracts\RedisConnectionFactory::class)
    ->connect(RedisConfig::fromArray([
        'host' => 'redis',
        'port' => 6379,
        'database' => 0,
    ]));
```
