# bifrost/queue-redis

Adapter opcional de fila Redis para o Bifrost Framework.

Registre `RedisQueueExtension` com as opcoes `host`, `port`, `timeout`,
`password`, `database` e `prefix` conforme a necessidade da aplicacao.

Este pacote usa `bifrost/redis` para abrir e reutilizar conexoes Redis. Se
outra extensao registrar uma implementacao propria de `RedisConnectionFactory`,
a fila passa a usar essa factory automaticamente.
