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
| `bifrost/queue-worker` | Worker opcional para consumo de filas |
| `bifrost/database-pdo` | Fabrica PDO generica opcional |
| `bifrost/database-mysql` | Banco MySQL opcional |
| `bifrost/database-postgresql` | Banco PostgreSQL opcional |
| `bifrost/database-sqlite` | Banco SQLite opcional |
| `bifrost/datatype-core` | Base comum para DataTypes |
| `bifrost/datatype-*` | DataTypes opcionais instalados individualmente |
| `bifrost/datatypes` | Agregador opcional com todos os DataTypes |
| `bifrost/log-stdout` | Logs em stdout/stderr |
| `bifrost/log-file` | Logs em arquivo |
| `bifrost/log-mongodb` | Persistencia MongoDB opcional para documentos de log |
| `bifrost/storage-local` | Storage local opcional |
| `bifrost/storage-s3` | Storage S3 opcional |
| `bifrost/skeleton` | Projeto inicial para novas APIs |

## Inicio Rapido

```bash
mkdir api
cd api
composer create-project bifrost/skeleton .
cp .env.example .env
docker compose up --build
curl http://localhost:8080/health
```

A aplicacao inicial instala somente `bifrost/framework`. Para acrescentar
infraestrutura, instale apenas o adaptador necessario. Os pacotes nao formam
uma lista obrigatoria:

| Necessidade | Pacote |
| --- | --- |
| Cache local | `bifrost/cache-apcu` |
| Cache compartilhado | `bifrost/cache-redis` |
| Fila Redis | `bifrost/queue-redis` |
| Worker para consumir filas | `bifrost/queue-worker` |
| Banco MySQL | `bifrost/database-mysql` |
| Banco PostgreSQL | `bifrost/database-postgresql` |
| Um DataType especifico | `bifrost/datatype-email` |
| Todos os DataTypes | `bifrost/datatypes` |

O arquivo `core/config/extensions.php` do skeleton registra fila instalada e
adapters selecionados. Cache e selecionado por `CACHE_DRIVER=apcu|redis`;
banco, por `DB_DRIVER=mysql|postgresql`.

## Variantes Docker

Sem fila, cache ou banco:

```bash
docker compose up --build
```

Os arquivos em `core/compose/` sao overlays opcionais. Para ativa-los, informe
cada arquivo com `-f`; o Docker Compose combina as configuracoes na ordem
recebida. Nao e necessario copiar e colar o conteudo no `docker-compose.yml`.

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

## Estrutura da aplicacao

O skeleton cria a estrutura inicial da API dentro da pasta escolhida. O codigo
da aplicacao fica em `app/`, configuracoes de boot ficam em `core/`, e o
servidor HTTP expoe somente `public/`. Actions seguem a convencao
`/controller/action`; aliases opcionais ficam em `app/Http/HttpRoutes.php`.

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

## Documentacao

- [Documentacao humana](docs/html/index.html)

## Licenca

Distribuido sob a licenca MIT. Consulte [LICENSE](LICENSE).
