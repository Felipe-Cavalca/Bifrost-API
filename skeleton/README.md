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

O projeto inicial exige apenas PHP e `bifrost/framework`. A resposta inicial e:

```json
{"status":"ok"}
```

## Estrutura

```text
app/
|-- Attributes/       # Validacoes e hooks HTTP especificos da aplicacao.
|-- Contracts/        # Interfaces especificas do produto.
|-- DataTypes/        # Valores tipados com regras do dominio.
|-- Enums/            # Enumeracoes do dominio.
|-- Http/Controller/  # Entrada HTTP: recebe Request e devolve Response.
|-- Integrations/     # Clientes de APIs e sistemas externos.
|-- Repositories/     # Leitura e persistencia de dados externos ao dominio.
|-- Services/         # Regras de negocio e casos de uso.
|-- Support/          # Helpers internos pequenos e reutilizaveis.
`-- Worker/           # Handlers e registro de tarefas executadas em fila.
core/
|-- bootstrap/app.php     # Monta Application, extensoes e rotas.
|-- compose/              # Overlays opcionais do Docker Compose.
|-- config/extensions.php # Ativa somente pacotes opcionais instalados.
`-- routes/api.php        # Declara rotas HTTP.
public/index.php          # Unica entrada exposta pelo servidor HTTP.
worker.php                # Entrada CLI para consumo de filas.
tests/                    # Testes da aplicacao.
```

As pastas vazias com `.gitkeep` existem para manter um padrao previsivel entre
projetos. Remova apenas quando decidir oficialmente nao usar uma categoria.

### Exemplos didaticos

- `app/Attributes/Permission.php` mostra como bloquear uma action antes do
  controller. Em um projeto real, conecte o attribute ao service de autenticacao.
- `app/DataTypes/ProjectCode.php` mostra um DataType especifico da aplicacao que
  implementa `Insertable` para persistencia e `Responseable` para retorno JSON.
- `app/Http/Controller/HealthController.php` mostra o formato minimo de um
  controller.
- `app/Worker/ExampleTaskHandler.php` mostra como implementar uma tarefa de fila.

## Extensoes opcionais

O bootstrap registra automaticamente somente os pacotes instalados:

```bash
composer require bifrost/cache-redis bifrost/queue-redis
composer require bifrost/queue-worker
composer require bifrost/cache-apcu
composer require bifrost/database-mysql
# ou
composer require bifrost/database-postgresql
```

Defina a configuracao necessaria em `.env` e combine o Compose principal com o
overlay apropriado:

```bash
composer require bifrost/cache-apcu
docker compose -f docker-compose.yml -f core/compose/apcu.yml up --build

composer require bifrost/cache-redis bifrost/queue-redis
docker compose -f docker-compose.yml -f core/compose/redis.yml up --build

composer require bifrost/queue-worker
php worker.php --queue=default

composer require bifrost/database-mysql
docker compose -f docker-compose.yml -f core/compose/mysql.yml up --build

composer require bifrost/database-postgresql
docker compose -f docker-compose.yml -f core/compose/postgresql.yml up --build
```

Os complementos podem ser combinados:

```bash
docker compose -f docker-compose.yml -f core/compose/redis.yml -f core/compose/postgresql.yml up --build
```

Os pacotes `cache-redis` e `queue-redis` usam `bifrost/redis` internamente para
reutilizar conexoes equivalentes.

Os overlays instalam na imagem PHP apenas a extensao requerida pelo perfil.
Escolha `CACHE_DRIVER=apcu` ou `CACHE_DRIVER=redis`; nao registre ambos como
provider do mesmo contrato.
