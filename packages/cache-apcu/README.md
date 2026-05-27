# bifrost/cache-apcu

Adapter opcional de cache APCu para o Bifrost Framework.

O pacote requer a extensao PHP `apcu`. Em execucoes pela CLI, habilite
`apc.enable_cli=1` quando o cache precisar estar ativo.

Registre `ApcuCacheExtension` com as opcoes `prefix` e `ttl`. Sem `ttl`, os
itens permanecem armazenados sem expiracao, como no adapter Redis modular.
Defina `ttl` como `3600` para reproduzir o tempo padrao da integracao APCu
anterior.

```php
use Bifrost\Extension\CacheApcu\ApcuCacheExtension;

$application->extend(new ApcuCacheExtension([
    'prefix' => 'app:',
    'ttl' => 3600,
]));
```

O pacote implementa `Bifrost\Framework\Contracts\CacheStore`. Registre
somente uma extensao de cache na aplicacao, pois os adapters de cache
compartilham esse contrato.
