# bifrost/cache-redis

Adapter opcional de cache Redis para o Bifrost Framework.

Registre `RedisCacheExtension` com as opcoes `host`, `port`, `timeout`,
`password`, `database` e `prefix` conforme a necessidade da aplicacao.

Este pacote usa `bifrost/redis` para abrir e reutilizar conexoes Redis. Se
outra extensao registrar uma implementacao propria de `RedisConnectionFactory`,
o cache passa a usar essa factory automaticamente.
