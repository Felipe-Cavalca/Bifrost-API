# Bifrost Framework

O Bifrost é um framework PHP distribuído por Composer para criar APIs novas
com core pequeno, extensões opcionais e um skeleton de projeto.

## Pacotes

| Pacote | Finalidade |
| --- | --- |
| `bifrost/framework` | Kernel HTTP, request/response, rotas, middleware, container e contratos |
| `bifrost/cache-apcu` | Cache APCu opcional |
| `bifrost/redis` | Conexão Redis reutilizável para extensões opcionais |
| `bifrost/cache-redis` | Cache Redis opcional |
| `bifrost/queue-redis` | Fila Redis opcional |
| `bifrost/queue-worker` | Worker opcional para consumo de filas |
| `bifrost/database-pdo` | Fábrica PDO genérica opcional |
| `bifrost/database-mysql` | Banco MySQL opcional |
| `bifrost/database-postgresql` | Banco PostgreSQL opcional |
| `bifrost/database-sqlite` | Banco SQLite opcional |
| `bifrost/datatype-core` | Base comum para DataTypes |
| `bifrost/datatype-*` | DataTypes opcionais instalados individualmente |
| `bifrost/datatypes` | Agregador opcional com todos os DataTypes |
| `bifrost/log-stdout` | Logs em stdout/stderr |
| `bifrost/log-file` | Logs em arquivo |
| `bifrost/log-mongodb` | Persistência MongoDB opcional para documentos de log |
| `bifrost/storage-local` | Storage local opcional |
| `bifrost/storage-s3` | Storage S3 opcional |
| `bifrost/skeleton` | Projeto inicial para novas APIs |

## Início rápido

```bash
mkdir api
cd api
composer create-project bifrost/skeleton .
cp .env.example .env
docker compose up --build
curl http://localhost:8080/health
```

A aplicação inicial instala somente `bifrost/framework`. Para acrescentar
infraestrutura, instale apenas o adaptador necessário. Os pacotes não formam
uma lista obrigatória:

| Necessidade | Pacote |
| --- | --- |
| Cache local | `bifrost/cache-apcu` |
| Cache compartilhado | `bifrost/cache-redis` |
| Fila Redis | `bifrost/queue-redis` |
| Worker para consumir filas | `bifrost/queue-worker` |
| Banco MySQL | `bifrost/database-mysql` |
| Banco PostgreSQL | `bifrost/database-postgresql` |
| Um DataType específico | `bifrost/datatype-email` |
| Todos os DataTypes | `bifrost/datatypes` |

O arquivo `core/config/extensions.php` do skeleton registra fila instalada e
adapters selecionados. Cache é selecionado por `CACHE_DRIVER=apcu|redis`;
banco, por `DB_DRIVER=mysql|postgresql`; logs, por
`LOG_DRIVER=stdout|file|mongodb`.

SQLite também é opcional, mas exige registro explícito no boot da aplicação.

## Variantes Docker

Sem fila, cache ou banco:

```bash
docker compose up --build
```

Os arquivos em `core/compose/` são overlays opcionais. Para ativá-los, informe
cada arquivo com `-f`; o Docker Compose combina as configurações na ordem
recebida. Não é necessário copiar e colar o conteúdo no `docker-compose.yml`.

Com cache APCu:

```bash
composer require bifrost/cache-apcu
docker compose -f docker-compose.yml -f core/compose/apcu.yml up --build
```

Com cache Redis e fila Redis opcional:

```bash
composer require bifrost/cache-redis bifrost/queue-redis
docker compose -f docker-compose.yml -f core/compose/redis.yml up --build
```

O overlay atual de Redis ativa `CACHE_DRIVER=redis`; por isso ele exige
`bifrost/cache-redis`. Um perfil Docker para fila Redis isolada ainda não faz
parte do skeleton.

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

Cada complemento instala na imagem PHP somente a extensão exigida pelo
pacote escolhido (`apcu`, `redis`, `pdo_mysql` ou `pdo_pgsql`).

## Estrutura da aplicação

O skeleton cria a estrutura inicial da API dentro da pasta escolhida. O código
da aplicação fica em `app/`, configurações de boot ficam em `core/`, e o
servidor HTTP expõe somente `public/`. Actions seguem a convenção
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

## Documentação

- [Documentação humana](docs/html/index.html)

## Licença

Distribuído sob a licença MIT. Consulte [LICENSE](LICENSE).
