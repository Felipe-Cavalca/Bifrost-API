# Arquitetura

## Objetivo

O Bifrost-API possui um runtime compativel consumido pelo repositorio agregador
`Felipe-Cavalca/Bifrost` e uma distribuicao Composer modular em evolucao. O
novo nucleo deve ser pequeno e independente de regras de aplicacao sem quebrar
a superficie existente em `api/`.

## Estrutura Oficial

```text
.
|-- api/                            # Runtime compativel atual
|-- packages/framework/             # Nucleo e contratos publicos
|-- packages/cache-apcu/            # Adaptador de cache APCu
|-- packages/cache-redis/           # Adaptador de cache Redis
|-- packages/queue-redis/           # Adaptador de fila Redis
|-- packages/database-pdo/          # Base PDO
|-- packages/database-mysql/        # Configuracao PDO para MySQL
|-- packages/database-postgresql/   # Configuracao PDO para PostgreSQL
|-- packages/log-mongodb/           # Sink MongoDB para log estruturado
|-- packages/storage-local/         # Storage local modular
|-- packages/storage-s3/            # Storage S3 modular
|-- skeleton/                       # Aplicacao inicial consumidora
|-- docker/modular/                 # Ambiente de verificacao do monorepo
|-- docs/                           # Padroes e guias
`-- .github/workflows/              # CI
```

## Compatibilidade com o Agregador

O repositorio `Felipe-Cavalca/Bifrost` combina historicos de modulos como
`Bifrost-API`, `Bifrost-Database` e `Bifrost-Front`. Enquanto esse fluxo nao
for migrado para Composer:

- `api/` preserva os namespaces `Bifrost\*`, entrypoint e Docker existentes;
- os checks existentes de `api/` continuam obrigatorios para alteracoes nessa
  superficie;
- `packages/` usa `Bifrost\Framework\*` e `Bifrost\Extension\*` sem
  substituir classes legadas;
- remover artefatos de `api/` exige migracao coordenada nos consumidores.

Superficie compativel preservada:

| Area atual | Caminhos preservados |
| --- | --- |
| Entry point e request | `api/index.php`, `api/Core/Request.php` |
| Routing/controllers | `api/Enum/Routes.php`, `api/Controller/` |
| Cache e fila | `api/Core/Cache.php`, `api/Core/Queue.php`, `api/Worker.php` |
| Banco e storage | `api/Core/Database.php`, `api/Integration/` |
| Configuracao e observabilidade | `api/Core/Settings.php`, `api/Core/Logger.php` |
| Testes e imagem | `api/tests/`, `api/Docker/` |

## Separacao Framework e Aplicacao

`bifrost/framework` contem:

- `Application`, `HttpKernel` e `Container`;
- `Request`, `Response` e `ResponseEmitter`;
- `Router`, `Route` e resolucao de controllers;
- contratos `Extension`, `CacheStore`, `Queue` e
  `DatabaseConnectionFactory` e `Storage`.

`bifrost/framework` nao contem:

- controllers da aplicacao;
- rotas reais;
- entrypoint publico;
- configuracao de ambiente;
- clientes Redis ou conexoes PDO concretas.

`bifrost/skeleton` contem os elementos de aplicacao:

- `public/index.php`;
- `bootstrap/app.php`;
- `config/extensions.php`;
- `routes/api.php`;
- controllers sob `app/`;
- compose base e complementos opcionais.

O runtime compativel em `api/` mantem suas camadas atuais de request,
attributes, settings, storage, logging, fila e banco ate que cada contrato
tenha caminho de migracao consumivel pelo agregador.

## Bootstrap e Lifecycle

O entrypoint deve permanecer minimo:

```text
public/index.php
  -> vendor/autoload.php
  -> bootstrap/app.php
  -> Request::fromGlobals()
  -> Application::handle()
  -> Application::emit()
```

O bootstrap cria a aplicacao, carrega extensoes instaladas e registra rotas.
O kernel aplica middleware, resolve a rota, invoca o controller e sempre
entrega um `Response`.

## Extensoes

Integracoes externas implementam `Bifrost\Framework\Contracts\Extension` e
registram seus contratos no container. Uma aplicacao sem extensoes executa
HTTP normalmente.

| Necessidade | Contrato do core | Pacote |
| --- | --- | --- |
| Cache APCu | `CacheStore` | `bifrost/cache-apcu` |
| Cache Redis | `CacheStore` | `bifrost/cache-redis` |
| Fila Redis | `Queue` | `bifrost/queue-redis` |
| PDO generico | `DatabaseConnectionFactory` | `bifrost/database-pdo` |
| MySQL | `DatabaseConnectionFactory` | `bifrost/database-mysql` |
| PostgreSQL | `DatabaseConnectionFactory` | `bifrost/database-postgresql` |
| Log MongoDB | contrato local do pacote | `bifrost/log-mongodb` |
| Storage local | `Storage` | `bifrost/storage-local` |
| Storage S3 | `Storage` | `bifrost/storage-s3` |

Novos fornecedores devem nascer como pacotes separados quando adicionarem
extensao PHP, SDK, servico externo ou configuracao propria.

`cache-apcu` e `cache-redis` implementam o mesmo contrato; uma aplicacao deve
selecionar somente um provider de cache. `log-mongodb` permanece aditivo com
contrato local ate existir contrato comum de logging no framework.

O contrato modular `Storage` nao e substituto binario do contrato legado:
recebe chave `string`, usa `DateTimeImmutable` e expoe `temporaryUrl()`,
enquanto `Bifrost\Interface\Storage` permanece compativel em `api/`.

## Composer e Namespaces

- Todo pacote possui seu proprio `composer.json`.
- O autoload publico usa somente PSR-4; nao use `classmap` para codigo fonte.
- Nucleo: `Bifrost\Framework\`.
- Extensoes: `Bifrost\Extension\<Modulo>\`.
- Aplicacao gerada: `App\`.
- Dependencias opcionais nao devem entrar em `bifrost/framework`.

## Verificacoes

A verificacao oficial do monorepo executa os pacotes e o skeleton com Redis,
MySQL e PostgreSQL reais temporarios:

```bash
docker compose -f docker/modular/docker-compose.test.yml run --rm --build tests
```

Alteracoes em core, contrato ou lifecycle devem incluir testes no pacote
afetado e validar o conjunto modular completo. Alteracoes sob `api/` tambem
devem executar os checks e testes legados correspondentes.

```bash
docker compose -f api/Docker/docker-compose.dev.yml run --rm api1 composer check
docker compose -f api/Docker/docker-compose.dev.yml run --rm api1 composer test
```
