# Bifrost API

Bifrost-API e um micro-framework em PHP 8.1+ focado no nucleo HTTP da aplicacao: roteamento, atributos, respostas tipadas, cache, fila, integracao com banco via PDO e utilitarios de validacao.

O codigo principal fica em `api/` e a suite de testes fica em `api/tests/`.

## Indice

1. [Visao geral](#visao-geral)
2. [Estrutura do projeto](#estrutura-do-projeto)
3. [Requisitos](#requisitos)
4. [Execucao local](#execucao-local)
5. [Variaveis de ambiente](#variaveis-de-ambiente)
6. [Como funciona o request](#como-funciona-o-request)
7. [Atributos disponiveis](#atributos-disponiveis)
8. [Banco, cache, fila e S3](#banco-cache-fila-e-s3)
9. [Testes](#testes)
10. [CI em Pull Request](#ci-em-pull-request)
11. [Contribuicao](#contribuicao)
12. [Licenca](#licenca)

## Visao geral

Recursos disponiveis hoje no projeto:

- Roteamento por `_controller` e `_action`, com suporte a mapeamento via enum de rotas.
- Attributes para validacao e comportamento transversal, como `Method`, `Cache`, `RequiredParams` e `Transaction`.
- Respostas HTTP tipadas via `Bifrost\Class\HttpResponse`.
- Integracao com banco de dados por PDO para `sqlite`, `mysql` e `pgsql`.
- Cache e fila baseados em Redis, com fallback seguro quando Redis nao estiver configurado.
- Integracao opcional com S3 via AWS SDK for PHP.
- Testes automatizados com PHPUnit.

## Estrutura do projeto

```text
.
├── .github/workflows/       # Workflows do GitHub Actions
├── .env.example             # Exemplo de configuracao
├── api/
│   ├── Attributes/          # Attributes da framework
│   ├── Class/               # Classes utilitarias, como HttpResponse
│   ├── Controller/          # Controllers HTTP
│   ├── Core/                # Kernel, request, settings, cache, queue
│   ├── DataTypes/           # Tipos auxiliares validados
│   ├── Docker/              # Dockerfiles e compose da API
│   ├── Enum/                # Enums do projeto
│   ├── Include/             # Traits e helpers
│   ├── Integration/         # Integracoes externas
│   ├── Interface/           # Contratos
│   ├── Model/               # Reservado para modelos
│   ├── Tasks/               # Reservado para tarefas assicronas
│   ├── tests/               # Suite PHPUnit
│   ├── composer.json
│   ├── index.php
│   ├── phpunit.xml
│   └── Worker.php
└── README.md
```

## Requisitos

Para rodar localmente sem Docker:

- PHP 8.1 ou superior
- Composer
- Extensoes PHP exigidas pelo projeto:
  - `curl`
  - `json`
- Para alguns cenarios:
  - `pdo_sqlite`, `pdo_mysql` ou `pdo_pgsql`
  - `redis`, se quiser cache e fila reais

Para rodar com Docker:

- Docker
- Docker Compose

## Execucao local

### Com Docker

1. Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

2. Suba o ambiente de desenvolvimento:

```bash
docker compose -f api/Docker/docker-compose.dev.yml up -d --build
```

O Nginx sobe na porta `80` e encaminha as requisicoes para dois workers PHP-FPM: `api1` e `api2`.

Em producao, o compose usa a imagem definida em `BFR_API_IMAGE`:

```bash
BFR_API_IMAGE=ghcr.io/felipe-cavalca/bifrost-api:latest \
docker compose -f api/Docker/docker-compose.prod.yml up -d
```

### Sem Docker

1. Instale as dependencias PHP:

```bash
cd api
composer install
```

2. Ajuste as variaveis de ambiente no arquivo `.env` da raiz, se necessario.

3. Execute com o servidor embutido do PHP para desenvolvimento simples:

```bash
cd api
php -S 0.0.0.0:8000
```

Depois acesse:

```bash
curl "http://localhost:8000/index.php?_controller=index&_action=index"
```

Resposta atual do controller padrao:

```json
{
  "status": 200,
  "message": "Operation completed successfully",
  "data": null,
  "errors": null
}
```

Se estiver usando o Nginx do projeto, o acesso tipico fica em:

```bash
curl "http://localhost/api/index/index"
```

## Variaveis de ambiente

O projeto le as configuracoes principalmente via `api/Core/Settings.php` e pelas integracoes em `api/Integration/`.

Principais variaveis:

| Nome | Uso |
|------|-----|
| `BFR_API_DISPLAY_ERRORS` | Liga ou desliga exibicao de erros do PHP |
| `BFR_API_SESSION_SAVE_HANDLER` | Handler de sessao |
| `BFR_API_SESSION_SAVE_PATH` | Local de persistencia de sessao |
| `BFR_API_SESSION_GC_MAXLIFETIME` | TTL da sessao |
| `BFR_API_SESSION_COOKIE_LIFETIME` | TTL do cookie de sessao |
| `BFR_API_SQL_DRIVER` | Driver do banco: `sqlite`, `mysql` ou `pgsql` |
| `BFR_API_SQL_HOST` | Host do banco |
| `BFR_API_SQL_PORT` | Porta do banco |
| `BFR_API_SQL_DATABASE` | Nome do banco ou path do arquivo SQLite |
| `BFR_API_SQL_USER` | Usuario do banco |
| `BFR_API_SQL_PASSWORD` | Senha do banco |
| `BFR_API_REDIS_HOST` | Host do Redis |
| `BFR_API_REDIS_PORT` | Porta do Redis |
| `BFR_API_REDIS_QUEUE` | Nome da fila Redis |
| `BFR_API_S3_BUCKET` | Bucket S3 |
| `BFR_API_S3_REGION` | Regiao do bucket |
| `BFR_API_S3_KEY` | Access key S3 |
| `BFR_API_S3_SECRET` | Secret key S3 |
| `BFR_API_S3_ENDPOINT` | Endpoint customizado de S3 |
| `BFR_API_S3_PATH_STYLE` | Habilita path-style endpoint |
| `BFR_API_IMAGE` | Imagem usada no compose de producao |
| `BFR_API_HTTP_PORT` | Porta exposta pelo Nginx em producao |

O arquivo [`.env.example`](/workspaces/Bifrost-API/.env.example) tem um ponto de partida para configuracao local.

## Como funciona o request

O ponto de entrada HTTP e [`api/index.php`](/workspaces/Bifrost-API/api/index.php), que instancia `Bifrost\Core\Request`.

O fluxo atual e:

1. `Get` le `_controller` e `_action`.
2. `Request` resolve o controller e a action.
3. Os attributes do metodo sao instanciados.
4. Attributes `before()` podem bloquear a execucao e devolver uma resposta imediatamente.
5. A action do controller e executada.
6. Attributes `after()` recebem a resposta final.

Controller padrao atual:

- Arquivo: [`api/Controller/index.php`](/workspaces/Bifrost-API/api/Controller/index.php)
- Classe: `Bifrost\Controller\Index`
- Action padrao: `index()`

## Atributos disponiveis

Attributes implementados hoje:

| Attribute | Finalidade |
|-----------|------------|
| `Method` | Restringe os metodos HTTP permitidos e responde a `OPTIONS` |
| `Cache` | Usa GET, POST e sessao para gerar chave de cache |
| `Details` | Expoe metadados do endpoint |
| `RequiredParams` | Exige parametros GET e valida tipos |
| `OptionalParams` | Valida parametros GET opcionais |
| `RequiredFields` | Exige campos do corpo e valida tipos |
| `OptionalFields` | Valida campos opcionais do corpo |
| `Response` | Documenta o shape esperado da resposta |
| `Transaction` | Abre transacao antes da action e faz commit ou rollback depois |

Os metadados podem ser lidos em runtime com:

```php
Request::getOptionsAttributes('index', 'index');
```

## Banco, cache, fila e S3

### Banco

O projeto usa [`Bifrost\Core\Database`](/workspaces/Bifrost-API/api/Core/Database.php), que herda de [`PdoDatabase`](/workspaces/Bifrost-API/api/Integration/Database/PdoDatabase.php).

Capacidades atuais:

- `select`
- `insert`
- `update`
- `delete`
- `exists`
- `query`
- introspeccao de tabelas e colunas

Drivers suportados:

- SQLite
- MySQL
- PostgreSQL

### Cache

[`Bifrost\Core\Cache`](/workspaces/Bifrost-API/api/Core/Cache.php) usa Redis quando configurado.

Se Redis nao estiver configurado:

- `set`, `exists` e `del` retornam fallback seguro
- a leitura com valor padrao continua funcionando

### Fila

[`Bifrost\Core\Queue`](/workspaces/Bifrost-API/api/Core/Queue.php) usa Redis quando disponivel.

Se Redis nao estiver configurado:

- `addToFront`
- `addToEnd`
- `addScheduledTask`

executam a `Task` imediatamente no processo atual.

O worker da fila fica em [`api/Worker.php`](/workspaces/Bifrost-API/api/Worker.php).

### S3

A integracao S3 e opcional e fica em [`api/Integration/S3Storage.php`](/workspaces/Bifrost-API/api/Integration/S3Storage.php).

Para usa-la, instale o SDK oficial:

```bash
cd api
composer require aws/aws-sdk-php
```

Exemplo:

```php
use Bifrost\Integration\S3Storage;

$storage = S3Storage::fromSettings();
$storage->put('documents/report.txt', 'conteudo', [
    'ContentType' => 'text/plain',
]);
```

## Testes

Os testes automatizados ficam em `api/tests`.

Execucao local:

```bash
cd api
./vendor/bin/phpunit -c phpunit.xml
```

Resultado validado atualmente:

```text
OK (55 tests, 187 assertions)
```

A suite cobre o codigo PHP atual do projeto, incluindo:

- attributes
- core
- enums
- datatypes
- controller padrao
- integracoes
- contratos
- entrypoints

## CI em Pull Request

O repositorio agora possui um workflow dedicado em [`.github/workflows/php-tests-pr.yml`](/workspaces/Bifrost-API/.github/workflows/php-tests-pr.yml).

Quando um Pull Request e:

- aberto
- reaberto
- atualizado
- marcado como `ready for review`

o GitHub Actions:

1. prepara PHP 8.3
2. instala dependencias, se necessario
3. executa a suite PHPUnit em `api/`
4. publica o status nos `Checks` do PR
5. adiciona ou atualiza um comentario no PR com `passed` ou `failed`

## Contribuicao

Fluxo sugerido:

1. Crie uma branch a partir de `main`.
2. Faça as alteracoes necessarias.
3. Rode os testes locais.
4. Abra o Pull Request.
5. Verifique os `Checks` do GitHub Actions.

Se a contribuicao vier de um fork, os checks continuam rodando. O comentario automatico no PR pode depender das permissoes disponiveis no repositorio.

## Licenca

Distribuido sob a licenca MIT. Consulte [LICENSE](/workspaces/Bifrost-API/LICENSE).
