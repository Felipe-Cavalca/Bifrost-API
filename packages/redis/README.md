# bifrost/redis

Cliente Redis opcional, encapsulado e reutilizavel para extensoes do Bifrost Framework.

Use este pacote quando uma extensao precisar de Redis sem depender da classe
nativa `Redis`. `RedisConnectionManager` reutiliza o mesmo cliente para
configuracoes iguais e permite trocar a factory por uma implementacao futura
de cluster, roteamento de leitura/escrita ou balanceamento.

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
