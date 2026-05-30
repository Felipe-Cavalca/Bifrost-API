# Bifrost Framework

O Bifrost e um framework PHP distribuido por Composer para criar APIs novas
com core pequeno, extensoes opcionais e um skeleton de projeto.

## Pacotes

| Pacote | Finalidade |
| --- | --- |
| `bifrost/framework` | Kernel HTTP, request/response, rotas, middleware, container e contratos |
| `bifrost/cache-apcu` | Cache APCu opcional |
| `bifrost/redis` | Conexao Redis reutilizavel para extensoes opcionais |
| `bifrost/cache-redis` | Cache Redis opcional |
| `bifrost/queue-redis` | Fila Redis opcional |
| `bifrost/database-pdo` | Fabrica PDO generica opcional |
| `bifrost/database-mysql` | Banco MySQL opcional |
| `bifrost/database-postgresql` | Banco PostgreSQL opcional |
| `bifrost/datatype-core` | Base comum para DataTypes |
| `bifrost/datatype-email` | DataType Email opcional |
| `bifrost/datatype-cpf` | DataType CPF opcional |
| `bifrost/datatypes` | Agregador opcional com todos os DataTypes |
| `bifrost/log-mongodb` | Persistencia MongoDB opcional para documentos de log |
| `bifrost/storage-local` | Storage local opcional |
| `bifrost/storage-s3` | Storage S3 opcional |
| `bifrost/skeleton` | Projeto inicial para novas APIs |

## Inicio Rapido

Depois de publicados os pacotes Composer:

```bash
mkdir api
cd api
composer create-project bifrost/skeleton .
cp .env.example .env
docker compose up --build
curl http://localhost:8080/health
```

A aplicacao inicial instala somente `bifrost/framework`. Para acrescentar
infraestrutura, instale apenas o adaptador necessario:

```bash
composer require bifrost/cache-redis bifrost/queue-redis
composer require bifrost/cache-apcu
composer require bifrost/datatype-email
# ou, se quiser todos os DataTypes
composer require bifrost/datatypes
composer require bifrost/database-mysql
# ou
composer require bifrost/database-postgresql
```

O arquivo `core/config/extensions.php` do skeleton registra fila instalada e
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
docker compose -f docker-compose.yml -f core/compose/apcu.yml up --build
```

Com Redis para cache e/ou fila:

```bash
composer require bifrost/cache-redis bifrost/queue-redis
docker compose -f docker-compose.yml -f core/compose/redis.yml up --build
```

Com MySQL:

```bash
composer require bifrost/database-mysql
docker compose -f docker-compose.yml -f core/compose/mysql.yml up --build
```

Com PostgreSQL:

```bash
composer require bifrost/database-postgresql
docker compose -f docker-compose.yml -f core/compose/postgresql.yml up --build
```

Cada complemento instala na imagem PHP somente a extensao exigida pelo
pacote escolhido (`apcu`, `redis`, `pdo_mysql` ou `pdo_pgsql`).

## Estrutura

```text
.
|-- packages/
|   |-- framework/
|   |-- cache-apcu/
|   |-- redis/
|   |-- cache-redis/
|   |-- queue-redis/
|   |-- database-pdo/
|   |-- database-mysql/
|   |-- database-postgresql/
|   |-- datatype-core/
|   |-- datatype-email/
|   |-- datatypes/
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

Aplicacoes novas usam o skeleton modular dentro da pasta `api/` do sistema
consumidor.

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

- [Documentacao humana](docs/human/index.html)
- [Documentacao para IAs](docs/ias/index.md)

## Licenca

Distribuido sob a licenca MIT. Consulte [LICENSE](LICENSE).
