# Bifrost Skeleton

Projeto inicial para APIs HTTP criadas com `bifrost/framework`.

## Criacao

```bash
composer create-project bifrost/skeleton minha-api
cd minha-api
cp .env.example .env
docker compose up --build
curl http://localhost:8080/health
```

O projeto inicial exige apenas PHP e `bifrost/framework`; a resposta inicial e:

```json
{"status":"ok"}
```

## Extensoes Opcionais

O bootstrap registra automaticamente os pacotes instalados:

```bash
composer require bifrost/cache-redis bifrost/queue-redis
composer require bifrost/cache-apcu
composer require bifrost/database-mysql
# ou
composer require bifrost/database-postgresql
```

Defina a configuracao necessária em `.env` e inicie o servico complementar:

```bash
composer require bifrost/cache-apcu
docker compose -f docker-compose.yml -f compose/apcu.yml up --build
composer require bifrost/cache-redis bifrost/queue-redis
docker compose -f docker-compose.yml -f compose/redis.yml up --build
composer require bifrost/database-mysql
docker compose -f docker-compose.yml -f compose/mysql.yml up --build
composer require bifrost/database-postgresql
docker compose -f docker-compose.yml -f compose/postgresql.yml up --build
```

Os complementos podem ser combinados, por exemplo Redis e PostgreSQL:

```bash
docker compose -f docker-compose.yml -f compose/redis.yml -f compose/postgresql.yml up --build
```

Os overlays instalam na imagem PHP apenas a extensao requerida pelo perfil.
Escolha `CACHE_DRIVER=apcu` ou `CACHE_DRIVER=redis`; nao registre ambos como
provider do mesmo contrato.
Caso `DB_DRIVER` seja preenchido sem o pacote de banco instalado, o bootstrap
interrompe a inicializacao com uma mensagem de configuracao.

## Estrutura

```text
app/Http/Controller/HealthController.php
bootstrap/app.php
compose/
config/extensions.php
public/index.php
routes/api.php
```

- `public/index.php` recebe e emite HTTP.
- `bootstrap/app.php` cria a aplicacao e registra rotas/extensoes.
- `config/extensions.php` ativa somente extensoes instaladas.
- `app/` contem o codigo da aplicacao, usando o namespace `App\`.
