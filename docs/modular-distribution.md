# Distribuicao Modular

## Objetivo

O Bifrost distribui um nucleo HTTP pequeno e extensoes Composer independentes.
Uma aplicacao instala somente infraestrutura que realmente utiliza.

Essa distribuicao e aditiva ao runtime `api/`, que continua disponivel para o
repositorio agregador `Felipe-Cavalca/Bifrost` durante a migracao.

## Projeto Inicial

```bash
composer create-project bifrost/skeleton minha-api
cd minha-api
cp .env.example .env
docker compose up --build
curl http://localhost:8080/health
```

O `skeleton` entrega:

- namespace PSR-4 `App\`;
- entrypoint em `public/index.php`;
- bootstrap em `bootstrap/app.php`;
- extensoes opcionais em `config/extensions.php`;
- rotas em `routes/api.php`;
- `GET /health`;
- Docker Compose base e complementos de infraestrutura.

## Escolha de Modulos

| Necessidade | Instale | Compose complementar |
| --- | --- | --- |
| Somente HTTP | `bifrost/framework` | nenhum |
| Cache APCu | `bifrost/cache-apcu` | `compose/apcu.yml` |
| Cache Redis | `bifrost/cache-redis` | `compose/redis.yml` |
| Fila Redis | `bifrost/queue-redis` | `compose/redis.yml` |
| MySQL | `bifrost/database-mysql` | `compose/mysql.yml` |
| PostgreSQL | `bifrost/database-postgresql` | `compose/postgresql.yml` |
| Log MongoDB | `bifrost/log-mongodb` | configuracao da aplicacao |
| Storage local | `bifrost/storage-local` | volume da aplicacao |
| Storage S3 | `bifrost/storage-s3` | credenciais/endpoint S3 |

Outras integracoes opcionais podem ser instaladas quando publicadas, sem
aumentar as dependencias do nucleo.

Os complementos Compose adicionam ao build PHP somente a extensao requerida
pelo perfil utilizado: `apcu`, `redis`, `pdo_mysql` ou `pdo_pgsql`.

## Bootstrap

O projeto base executa sem banco, cache ou fila. O arquivo
`config/extensions.php` registra a fila quando instalada e ativa cache/banco
somente quando o driver for selecionado:

```dotenv
CACHE_DRIVER=apcu
CACHE_PREFIX=bifrost:cache:
CACHE_TTL=60
```

Use `CACHE_DRIVER=redis` com `REDIS_HOST` e `REDIS_PORT` para o adapter Redis.
Somente um provider de cache deve ser configurado.

O banco exige a selecao explicita do driver:

```dotenv
DB_DRIVER=postgresql
DB_HOST=postgresql
DB_PORT=5432
DB_DATABASE=app
DB_USERNAME=app
DB_PASSWORD=app
```

Para Redis:

```dotenv
REDIS_HOST=redis
REDIS_PORT=6379
CACHE_PREFIX=bifrost:cache:
QUEUE_PREFIX=bifrost:queue:
```

Configurar `DB_DRIVER` sem instalar o pacote de banco correspondente interrompe
o bootstrap com mensagem objetiva, evitando uma aplicacao iniciada sem a
conexao esperada.

O pacote `bifrost/log-mongodb` recebe documentos prontos de log e nao
substitui `Bifrost\Core\Logger` no runtime compativel.

Os pacotes `bifrost/storage-local` e `bifrost/storage-s3` implementam o novo
contrato `Bifrost\Framework\Contracts\Storage`. Eles sao instalados de forma
opt-in e nao substituem automaticamente o contrato de storage de `api/`.

## Ambiente do Monorepo

Enquanto desenvolve os pacotes no mesmo repositorio, os testes configuram
repositorios Composer do tipo `path` internamente:

```bash
docker compose -f docker/modular/docker-compose.test.yml run --rm --build tests
```

Essa configuracao nao faz parte do `skeleton` publicado. O Dockerfile entregue
ao consumidor resolve `bifrost/framework` pelo repositorio Composer publicado.

## Publicacao

Cada pacote e o skeleton devem ser publicados por split para repositorios com
seu `composer.json` na raiz e tags SemVer independentes. O fluxo completo esta
definido em [releasing.md](releasing.md).
