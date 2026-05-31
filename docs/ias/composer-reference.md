# Referencia Composer do Bifrost para IAs

## Objetivo

Use esta referencia para criar e manter projetos novos com `bifrost/skeleton`
e os pacotes em `packages/`. A documentacao descreve apenas a distribuicao
Composer nova.

## Instalacao

```bash
mkdir api
cd api
composer create-project bifrost/skeleton .
cp .env.example .env
docker compose up --build
curl http://localhost:8080/health
```

## Lifecycle HTTP

1. `public/index.php` cria `Request::fromGlobals()`.
2. `core/bootstrap/app.php` cria `Application`, registra extensoes e rotas.
3. `HttpKernel` executa middlewares e resolve a rota.
4. `ControllerResolver` valida attributes e chama o handler.
5. O resultado vira `Response`.
6. `ResponseEmitter` envia status, headers e body.

Toda resposta recebe `X-Request-Id`. Erros JSON tambem recebem `request_id`.

## Controllers e Responses

- Controllers recebem `Bifrost\Framework\Http\Request`.
- Controllers retornam `Response`, `Responseable`, array ou string.
- Use helpers de `Response`: `json`, `text`, `created`, `notFound`,
  `badRequest`, `internalServerError`.
- Implemente `Bifrost\Framework\Contracts\Responseable` em objetos de dominio
  que podem ser enviados diretamente como JSON, como usuarios ou arquivos.
- `Responseable` e opt-in explicito: exponha somente campos seguros em
  `jsonSerialize()`.
- `HttpException` padroniza erro HTTP com status, erros e headers.

## Attributes

- `Method`: valida verbo HTTP.
- `RequiredFields` e `OptionalFields`: validam body.
- `RequiredParams` e `OptionalParams`: validam query.
- `Details`: metadados para introspeccao.
- `Cache`: hook before/after usando `CacheStore`.
- `Transaction`: hook before/after usando `TransactionManager`.

Attributes de lifecycle implementam `BeforeRequestAttribute` ou
`AfterResponseAttribute`.

Projetos podem criar attributes proprios em `app/Attributes/`. Use isso para
regras como permissao, autenticacao ou validacoes especificas do produto.

## DataTypes

DataTypes implementam `Bifrost\Framework\Contracts\DataType`.
DataTypes reutilizaveis tambem implementam `Insertable`, entao podem ser
passados para o banco e convertidos para `value()` antes de executar SQL.
DataTypes baseados em `AbstractDataType` tambem implementam `Responseable` e
podem ser retornados diretamente por controllers.

Use quando o valor possui regra propria ou aparece em mais de um fluxo.
Exemplos: `Email`, `Uuid`, `Cpf`, `Cnpj`, `Url`, `Json`, `Base64`,
`FileName`, `FilePath`, `FolderName`, `FolderPath`, `StorageKey`.

DataTypes especificos do produto ficam em `app/DataTypes/`. Se puderem ser
persistidos, implemente tambem `Insertable`.
Se puderem ser retornados diretamente como JSON, implemente tambem
`Responseable`.

## Cache

- Contrato: `Bifrost\Framework\Contracts\CacheStore`.
- APCu: `bifrost/cache-apcu`.
- Redis: `bifrost/cache-redis`.
- `cache-redis` depende automaticamente de `bifrost/redis`.

## Redis compartilhado

`bifrost/redis` fornece:

- `RedisConfig`
- `RedisClient`
- `RedisConnectionFactory`
- `NativeRedisClient`
- `NativeRedisConnectionFactory`
- `RedisConnectionManager`
- `RedisExtension`

Extensoes Redis devem receber `RedisClient` pela factory compartilhada e nao
usar a classe nativa `Redis` diretamente. O manager reutiliza o mesmo cliente
para configuracoes iguais. Implementacoes futuras de cluster, leitura/escrita
separadas ou balanceamento devem trocar a implementacao de `RedisClient`.

## Fila e Worker

- Contrato de fila: `Bifrost\Framework\Contracts\Queue`.
- Redis: `bifrost/queue-redis`.
- Worker: `bifrost/queue-worker`.
- Tarefas usam `TaskPayload`, `TaskHandler`, `TaskRegistry`,
  `QueueWorker` e `QueueWorkerCommand`.

## Banco PDO

- Contrato: `DatabaseConnectionFactory`.
- Base: `bifrost/database-pdo`.
- Drivers: `database-mysql`, `database-postgresql`, `database-sqlite`.
- `PdoConnectionFactory` reutiliza conexoes por nome.
- `PdoDatabase` tambem implementa `TransactionManager`.

## Storage

- Contrato: `Bifrost\Framework\Contracts\Storage`.
- Local: `bifrost/storage-local`.
- S3: `bifrost/storage-s3`.
- `storage-s3` fornece `S3ClientFactory` e `S3ClientManager`.
- Extensoes S3 devem usar `S3ClientFactory` em vez de criar `S3Client`
  diretamente.

## Logs

- Contrato: `Bifrost\Framework\Contracts\LogWriter`.
- Logger comum: `Bifrost\Framework\Logging\Logger`.
- Destinos opcionais: `log-stdout`, `log-file`, `log-mongodb`.
- O pacote MongoDB reutiliza o `MongoDB\Driver\Manager` dentro do writer.

## Regra para novas extensoes

1. Criar pacote em `packages/<nome>`.
2. Depender de `bifrost/framework`.
3. Implementar `Extension`.
4. Registrar contratos no container.
5. Encapsular fornecedor externo atras de factory/adapter.
6. Criar testes do pacote.
7. Atualizar `docs/html` e `docs/ias`.
