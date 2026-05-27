# Bifrost Framework

O Bifrost-API fornece o runtime PHP utilizado pelo repositorio agregador
[`Felipe-Cavalca/Bifrost`](https://github.com/Felipe-Cavalca/Bifrost) e uma
nova distribuicao Composer modular para novas aplicacoes.

O runtime existente em `api/` permanece compativel com o fluxo de mesclagem
do Bifrost. Os pacotes em `packages/` sao aditivos e nao removem namespaces,
entrypoints ou integracoes publicas atuais.

## Pacotes

| Pacote | Finalidade |
| --- | --- |
| `bifrost/framework` | Kernel HTTP, request/response, rotas, middleware, container e contratos |
| `bifrost/cache-apcu` | Cache APCu opcional |
| `bifrost/cache-redis` | Cache Redis opcional |
| `bifrost/queue-redis` | Fila Redis opcional |
| `bifrost/database-pdo` | Fabrica PDO generica opcional |
| `bifrost/database-mysql` | Banco MySQL opcional |
| `bifrost/database-postgresql` | Banco PostgreSQL opcional |
| `bifrost/log-mongodb` | Persistencia MongoDB opcional para documentos de log |
| `bifrost/storage-local` | Storage local opcional |
| `bifrost/storage-s3` | Storage S3 opcional |
| `bifrost/skeleton` | Projeto inicial para novas APIs |

## Compatibilidade Atual

O repositorio agregador Bifrost combina `Bifrost-API`, `Bifrost-Database` e
outros modulos por merge de branches `latest-release`. Por isso:

- `api/` continua sendo o runtime compativel existente;
- o workflow e a imagem Docker existentes continuam atendendo esse runtime;
- `packages/` evolui em paralelo ate uma migracao coordenada dos consumidores.

Validacao do runtime compativel:

```bash
docker compose -f api/Docker/docker-compose.dev.yml run --rm api1 composer test
```

## Inicio Rapido

Depois de publicados os pacotes Composer:

```bash
composer create-project bifrost/skeleton minha-api
cd minha-api
cp .env.example .env
docker compose up --build
curl http://localhost:8080/health
```

A aplicacao inicial instala somente `bifrost/framework`. Para acrescentar
infraestrutura, instale apenas o adaptador necessario:

```bash
composer require bifrost/cache-redis bifrost/queue-redis
composer require bifrost/cache-apcu
composer require bifrost/database-mysql
# ou
composer require bifrost/database-postgresql
```

O arquivo `config/extensions.php` do skeleton registra fila instalada e
adapters selecionados. Cache e selecionado por `CACHE_DRIVER=apcu|redis`;
banco, por `DB_DRIVER=mysql|postgresql`.

## Variantes Docker

Sem fila, cache ou banco:

```bash
docker compose up --build
```

Com cache APCu:

```bash
composer require bifrost/cache-apcu
docker compose -f docker-compose.yml -f compose/apcu.yml up --build
```

Com Redis para cache e/ou fila:

```bash
composer require bifrost/cache-redis bifrost/queue-redis
docker compose -f docker-compose.yml -f compose/redis.yml up --build
```

Com MySQL:

```bash
composer require bifrost/database-mysql
docker compose -f docker-compose.yml -f compose/mysql.yml up --build
```

Com PostgreSQL:

```bash
composer require bifrost/database-postgresql
docker compose -f docker-compose.yml -f compose/postgresql.yml up --build
```

Cada complemento instala na imagem PHP somente a extensao exigida pelo
pacote escolhido (`apcu`, `redis`, `pdo_mysql` ou `pdo_pgsql`).

## Estrutura

```text
.
|-- api/                      # Runtime compativel consumido pelo Bifrost
|-- packages/
|   |-- framework/
|   |-- cache-apcu/
|   |-- cache-redis/
|   |-- queue-redis/
|   |-- database-pdo/
|   |-- database-mysql/
|   |-- database-postgresql/
|   |-- log-mongodb/
|   |-- storage-local/
|   `-- storage-s3/
|-- skeleton/
|   |-- app/
|   |-- bootstrap/
|   |-- compose/
|   |-- config/
|   |-- public/
|   `-- routes/
|-- docker/modular/
|-- docs/
`-- .github/workflows/
```

Aplicacoes novas podem usar o skeleton modular. Aplicacoes integradas ao
repositorio Bifrost continuam usando `api/` ate a migracao ser coordenada.

Storage modular e opt-in: seus contratos nao substituem automaticamente o
contrato existente em `api/`.

## Lifecycle HTTP

```text
public/index.php
  -> Request::fromGlobals()
  -> Application
  -> HttpKernel
  -> middleware
  -> Router
  -> controller/callable
  -> Response
  -> ResponseEmitter
```

## Desenvolvimento

O ecossistema completo e testado em containers:

```bash
docker compose -f docker/modular/docker-compose.test.yml run --rm --build tests
```

O Dockerfile distribuido com o skeleton resolve pacotes pelo repositorio
Composer publicado. Durante desenvolvimento deste monorepo, use a verificacao
modular acima, que injeta dependencias locais por `path`.

## Documentacao

- [Arquitetura](docs/architecture.md)
- [Convencoes](docs/conventions.md)
- [Distribuicao modular](docs/modular-distribution.md)
- [Publicacao e versionamento](docs/releasing.md)

## Licenca

Distribuido sob a licenca MIT. Consulte [LICENSE](LICENSE).
