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
|-- Http/
|   |-- Controller/   # Entrada HTTP: recebe Request e devolve Response.
|   `-- HttpRoutes.php # Aliases e URLs que nao seguem /controller/action.
|-- Integrations/     # Clientes de APIs e sistemas externos.
|-- Repositories/     # Leitura e persistencia de dados externos ao dominio.
|-- Services/         # Regras de negocio e casos de uso.
|-- Support/          # Helpers internos pequenos e reutilizaveis.
`-- Worker/           # Handlers e registro de tarefas executadas em fila.
core/
|-- bootstrap/app.php     # Monta Application, extensoes e rotas.
|-- compose/              # Overlays opcionais do Docker Compose.
`-- config/extensions.php # Ativa somente pacotes opcionais instalados.
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
- `app/Http/HttpRoutes.php` mostra como registrar aliases opcionais. Actions que
  seguem `/controller/action` nao precisam ser cadastradas manualmente.
- `app/Worker/ExampleTaskHandler.php` mostra como implementar uma tarefa de fila.

## Extensoes opcionais

Instale somente os modulos usados pela aplicacao. O bootstrap registra
automaticamente as extensoes instaladas e selecionadas no `.env`.

| Necessidade | Pacote | Configuracao principal | Overlay |
| --- | --- | --- | --- |
| Cache local no processo PHP | `bifrost/cache-apcu` | `CACHE_DRIVER=apcu` | `core/compose/apcu.yml` |
| Cache compartilhado | `bifrost/cache-redis` | `CACHE_DRIVER=redis` | `core/compose/redis.yml` |
| Publicar tarefas em fila | `bifrost/queue-redis` | `REDIS_HOST=redis` | `core/compose/redis.yml` |
| Consumir tarefas da fila | `bifrost/queue-worker` | Nenhuma | Nenhum |
| Banco MySQL | `bifrost/database-mysql` | `DB_DRIVER=mysql` | `core/compose/mysql.yml` |
| Banco PostgreSQL | `bifrost/database-postgresql` | `DB_DRIVER=postgresql` | `core/compose/postgresql.yml` |
| Logs do container | `bifrost/log-stdout` | `LOG_DRIVER=stdout` | Nenhum |
| Logs em arquivo | `bifrost/log-file` | `LOG_DRIVER=file` | Nenhum |
| Logs em MongoDB | `bifrost/log-mongodb` | `LOG_DRIVER=mongodb` | Nenhum |

Cache Redis e fila Redis podem ser instalados juntos. Ambos reutilizam o pacote
base `bifrost/redis`, instalado automaticamente pelo Composer.

Defina a configuracao necessaria em `.env` e combine o Compose principal com o
overlay apropriado:

Combinar nao significa copiar e colar arquivos. Informe cada arquivo ao Docker
Compose com `-f`; ele aplica os overlays na ordem recebida sobre a configuracao
base de `docker-compose.yml`.

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

Storage e DataTypes tambem sao opcionais, mas nao sao selecionados pelo
bootstrap. Instale o pacote necessario quando o codigo da aplicacao for
utiliza-lo, por exemplo:

```bash
composer require bifrost/storage-s3
composer require bifrost/datatype-email
```
