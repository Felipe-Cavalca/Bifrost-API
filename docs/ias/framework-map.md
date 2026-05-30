# Mapa do Framework

## Core

- `Application`: objeto principal da aplicacao.
- `Container`: registro e resolucao de dependencias.
- `HttpKernel`: lifecycle HTTP.
- `Request`: metodo, path, query, body, headers e request-id.
- `Response`: JSON/texto/status/headers, helpers HTTP comuns e conversao do
  retorno de controllers.
- `HttpMethod`: enum de metodos HTTP suportados.
- `HttpStatusCode`: enum de status HTTP comuns e classificacao por familia.
- `Router` e `Route`: roteamento HTTP.
- `ControllerResolver`: invoca controllers e valida attributes.
- `HttpException`: excecao HTTP padronizada para respostas JSON com status, erros e headers.

## Observabilidade HTTP

- `Request` le `X-Request-Id` ou gera um identificador quando o header nao existe.
- `HttpKernel` inclui `X-Request-Id` em toda resposta.
- Respostas JSON de erro recebem `request_id` no payload.

## Attributes

- `Method`
- `Cache`
- `Transaction`
- `RequiredFields`
- `OptionalFields`
- `RequiredParams`
- `OptionalParams`
- `Details`
- `Response`

## Contracts

- `Extension`
- `CacheStore`
- `LogWriter`
- `Queue`
- `DatabaseConnectionFactory`
- `Storage`
- `DataType`
- `HttpAttribute`
- `RequestValidatorAttribute`
- `BeforeRequestAttribute`
- `AfterResponseAttribute`
- `TransactionManager`
- `LogWriter`
- `Insertable`
- `Responseable`

## Extensoes de Infraestrutura

- `bifrost/redis` fornece `RedisClient`, `RedisConnectionFactory` e `RedisConnectionManager`.
- Extensoes Redis devem depender de `RedisClient`, nunca da classe nativa `Redis`.
- A classe nativa `Redis` fica encapsulada em `NativeRedisClient`/`NativeRedisConnectionFactory`.
- Clientes Redis equivalentes sao reutilizados pelo `RedisConnectionManager`.
- Implementacoes futuras de cluster, roteamento de leitura/escrita ou balanceamento devem implementar `RedisClient`.
- `bifrost/storage-s3` fornece `S3ClientFactory` e `S3ClientManager`.
- Extensoes S3 devem usar `S3ClientFactory` em vez de criar `S3Client` diretamente.

## Ambiente Local

- O ambiente local oficial fica em `.devcontainer/`.
- A maquina local precisa apenas de Docker, Visual Studio Code e Dev Containers.
- O Dev Container instala PHP, Composer e extensoes necessarias aos pacotes.
- Redis, MySQL e PostgreSQL sobem por `.devcontainer/docker-compose.services.yml`.
- A suite modular completa roda dentro do Dev Container com
  `sh .devcontainer/check.sh`.
