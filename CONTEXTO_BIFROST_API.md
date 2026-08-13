# Contexto do Bifrost API

Este documento consolida o contexto conhecido sobre o projeto Bifrost API e a
direcao discutida para transformar o framework em pacotes Composer e
repositorios separados.

## Fonte deste contexto

O contexto abaixo foi montado a partir de:

- instrucoes globais e locais de agentes;
- `README.md`;
- `AGENTS.md`;
- `docs/ias/framework-map.md`;
- `docs/ias/packages.md`;
- `docs/ias/coding-rules.md`;
- `PLANO_REPOSITORIOS_GITHUB.md`;
- memoria local em `tmp/agent-memory/bifrost-agent-memory.sqlite`;
- estrutura atual de `packages/` e `skeleton/`.

Nao tenho acesso ao historico completo da conversa anterior fora do que esta
registrado no repositorio e na memoria local. Onde houver decisao ainda nao
confirmada no codigo, trate como plano ou premissa.

## Direcao geral

O projeto deixou de ser tratado como uma API unica e passou a ser tratado como
um framework PHP modular para criacao de APIs.

A direcao arquitetural atual e:

- distribuir o framework por Composer;
- manter um core pequeno em `packages/framework`;
- mover infraestrutura opcional para pacotes Composer independentes;
- manter DataTypes como pacotes modulares;
- manter um `skeleton/` para iniciar novos projetos;
- criar novos backends dentro de `api/` a partir do skeleton;
- evitar que regra de produto entre no framework;
- manter documentacao apenas da versao nova.

## Nome atual e nome planejado

O nome atual no repositorio ainda e Bifrost.

Pelo plano existente, `Bifrost` e um nome provisorio e nao deve ser publicado
como nome final dos pacotes ou repositorios.

A decisao recomendada registrada e:

- organizacao GitHub: `Elavora`;
- linha de repositorios: `Elavora/api-*`;
- vendor Composer: `elavora/api-*`;
- namespace PHP sugerido: `Elavora\Api`;
- nome publico sugerido: `Elavora API`.

Antes do primeiro push/publicacao dos repositorios finais, o plano recomenda
renomear:

- `bifrost/framework` para `elavora/api-framework`;
- `bifrost/skeleton` para `elavora/api-skeleton`;
- `bifrost/*` para `elavora/api-*`;
- `Bifrost\...` para `Elavora\Api\...`;
- `Bifrost Framework` para `Elavora API`;
- `Bifrost`/`bifrost` para o nome final adequado.

## Estrutura atual do repositorio

Areas principais:

- `packages/framework/`: nucleo Composer do framework;
- `packages/*/`: extensoes Composer opcionais;
- `packages/datatype-*/`: DataTypes modulares;
- `packages/datatypes/`: agregador dos DataTypes;
- `skeleton/`: projeto base para novas APIs;
- `api/`: referencia/area antiga de API;
- `docs/html/`: documentacao visual para humanos;
- `docs/ias/`: documentacao objetiva para agentes e IAs;
- `.devcontainer/`: ambiente oficial do monorepo modular.

O Docker antigo em `api/Docker/` e referencia antiga e nao deve ser usado como
modelo para a distribuicao Composer nova.

## Core do framework

Componentes registrados no mapa do framework:

- `Application`;
- `Container`;
- `HttpKernel`;
- `Request`;
- `Response`;
- `HttpMethod`;
- `HttpStatusCode`;
- `Router` e `Route`;
- `ConventionRouteResolver`;
- `ControllerResolver`;
- `HttpException`.

Lifecycle HTTP:

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

Rotas explicitas tem prioridade. Quando nenhuma rota corresponde a URL, o
fallback por convencao procura controllers em
`App\Http\Controller\{Nome}Controller` e action publica por
`/controller/action`. Quando a action e omitida, usa `index`.

## Observabilidade HTTP

Decisoes atuais:

- `Request` le `X-Request-Id` ou gera um identificador quando o header nao
  existe;
- `HttpKernel` inclui `X-Request-Id` em toda resposta;
- respostas JSON de erro incluem `request_id` no payload.

## Attributes e contratos

Attributes conhecidos:

- `Method`;
- `Cache`;
- `Transaction`;
- `RequiredFields`;
- `OptionalFields`;
- `RequiredParams`;
- `OptionalParams`;
- `Details`;
- `Response`.

Contratos conhecidos:

- `Extension`;
- `CacheStore`;
- `LogWriter`;
- `Queue`;
- `DatabaseConnectionFactory`;
- `Storage`;
- `DataType`;
- `HttpAttribute`;
- `RequestValidatorAttribute`;
- `BeforeRequestAttribute`;
- `AfterResponseAttribute`;
- `TransactionManager`;
- `Insertable`;
- `Responseable`.

## Pacotes atuais

Pacotes existentes em `packages/`:

| Modulo | Papel |
| --- | --- |
| `framework` | Core HTTP, container, rotas, middleware e contratos |
| `skeleton` | Projeto inicial, fora de `packages/` |
| `datatype-core` | Base comum para DataTypes |
| `datatype-email` | DataType Email |
| `datatype-url` | DataType Url |
| `datatype-base64` | DataType Base64 |
| `datatype-json` | DataType Json |
| `datatype-uuid` | DataType Uuid |
| `datatype-date-time` | DataType DateTime |
| `datatype-cpf` | DataType CPF |
| `datatype-cnpj` | DataType CNPJ |
| `datatype-file-name` | DataType FileName |
| `datatype-folder-name` | DataType FolderName |
| `datatype-file-path` | DataType FilePath |
| `datatype-folder-path` | DataType FolderPath |
| `datatype-storage-key` | DataType StorageKey |
| `datatypes` | Agregador dos DataTypes |
| `cache-apcu` | Cache local APCu |
| `redis` | Cliente Redis encapsulado e reutilizavel |
| `cache-redis` | Cache Redis |
| `queue-redis` | Fila Redis |
| `queue-worker` | Worker de filas |
| `database-pdo` | Base PDO |
| `database-mysql` | MySQL |
| `database-postgresql` | PostgreSQL |
| `database-sqlite` | SQLite |
| `storage-local` | Storage local |
| `storage-s3` | Storage S3 |
| `log-stdout` | Logs em stdout/stderr |
| `log-file` | Logs em arquivo |
| `log-mongodb` | Logs em MongoDB |

Regra principal: projetos consumidores devem instalar apenas o pacote
necessario para a feature usada.

## Repositorios planejados

Padrao recomendado:

- GitHub: `Elavora/api-<modulo>`;
- Composer: `elavora/api-<modulo>`;
- Namespace PHP: `Elavora\Api`.

Repositorios planejados:

| Modulo atual | Repositorio GitHub | Pacote Composer |
| --- | --- | --- |
| `framework` | `Elavora/api-framework` | `elavora/api-framework` |
| `skeleton` | `Elavora/api-skeleton` | `elavora/api-skeleton` |
| `datatype-core` | `Elavora/api-datatype-core` | `elavora/api-datatype-core` |
| `datatype-email` | `Elavora/api-datatype-email` | `elavora/api-datatype-email` |
| `datatype-url` | `Elavora/api-datatype-url` | `elavora/api-datatype-url` |
| `datatype-base64` | `Elavora/api-datatype-base64` | `elavora/api-datatype-base64` |
| `datatype-json` | `Elavora/api-datatype-json` | `elavora/api-datatype-json` |
| `datatype-uuid` | `Elavora/api-datatype-uuid` | `elavora/api-datatype-uuid` |
| `datatype-date-time` | `Elavora/api-datatype-date-time` | `elavora/api-datatype-date-time` |
| `datatype-cpf` | `Elavora/api-datatype-cpf` | `elavora/api-datatype-cpf` |
| `datatype-cnpj` | `Elavora/api-datatype-cnpj` | `elavora/api-datatype-cnpj` |
| `datatype-file-name` | `Elavora/api-datatype-file-name` | `elavora/api-datatype-file-name` |
| `datatype-folder-name` | `Elavora/api-datatype-folder-name` | `elavora/api-datatype-folder-name` |
| `datatype-file-path` | `Elavora/api-datatype-file-path` | `elavora/api-datatype-file-path` |
| `datatype-folder-path` | `Elavora/api-datatype-folder-path` | `elavora/api-datatype-folder-path` |
| `datatype-storage-key` | `Elavora/api-datatype-storage-key` | `elavora/api-datatype-storage-key` |
| `datatypes` | `Elavora/api-datatypes` | `elavora/api-datatypes` |
| `cache-apcu` | `Elavora/api-cache-apcu` | `elavora/api-cache-apcu` |
| `redis` | `Elavora/api-redis` | `elavora/api-redis` |
| `cache-redis` | `Elavora/api-cache-redis` | `elavora/api-cache-redis` |
| `queue-redis` | `Elavora/api-queue-redis` | `elavora/api-queue-redis` |
| `queue-worker` | `Elavora/api-queue-worker` | `elavora/api-queue-worker` |
| `database-pdo` | `Elavora/api-database-pdo` | `elavora/api-database-pdo` |
| `database-mysql` | `Elavora/api-database-mysql` | `elavora/api-database-mysql` |
| `database-postgresql` | `Elavora/api-database-postgresql` | `elavora/api-database-postgresql` |
| `database-sqlite` | `Elavora/api-database-sqlite` | `elavora/api-database-sqlite` |
| `storage-local` | `Elavora/api-storage-local` | `elavora/api-storage-local` |
| `storage-s3` | `Elavora/api-storage-s3` | `elavora/api-storage-s3` |
| `log-stdout` | `Elavora/api-log-stdout` | `elavora/api-log-stdout` |
| `log-file` | `Elavora/api-log-file` | `elavora/api-log-file` |
| `log-mongodb` | `Elavora/api-log-mongodb` | `elavora/api-log-mongodb` |

## Ordem pratica sugerida

Ordem recomendada para sair do monorepo atual para a distribuicao publica:

1. Definir o nome final.
2. Fazer replace de `bifrost`/`Bifrost` no projeto atual.
3. Rodar a suite completa no Dev Container com `sh .devcontainer/check.sh`.
4. Separar `packages/framework` em `Elavora/api-framework`.
5. Separar `skeleton` em `Elavora/api-skeleton`.
6. Publicar `elavora/api-framework`.
7. Publicar `elavora/api-skeleton`.
8. Testar `composer create-project elavora/api-skeleton api`.
9. Validar em um projeto consumidor real, como `dossier`.
10. Separar extensoes conforme estabilidade e necessidade.

Pacotes a priorizar:

1. `api-framework`;
2. `api-skeleton`;
3. `api-datatype-core`;
4. `api-datatypes`;
5. `api-database-pdo`;
6. `api-database-mysql`;
7. `api-database-postgresql`;
8. `api-cache-apcu`;
9. `api-redis`;
10. `api-cache-redis`;
11. `api-queue-redis`;
12. `api-queue-worker`;
13. `api-storage-local`;
14. `api-storage-s3`;
15. `api-log-stdout`;
16. `api-log-file`;
17. `api-log-mongodb`.

DataTypes individuais podem ser publicados depois, conforme necessidade real.

## Decisoes tecnicas registradas

Redis:

- `cache-redis` e `queue-redis` devem depender de `RedisClient`;
- a classe nativa `Redis` fica encapsulada em `NativeRedisClient` e
  `NativeRedisConnectionFactory`;
- `RedisConnectionManager` reutiliza clientes Redis equivalentes;
- implementacoes futuras de cluster, leitura/escrita ou balanceamento devem
  implementar `RedisClient`.

S3:

- `storage-s3` deve usar `S3ClientFactory`;
- extensoes S3 nao devem criar `S3Client` diretamente.

DataTypes:

- DataTypes de dominio devem ser usados em DTOs, services e regras de negocio;
- controllers podem receber dados brutos, mas devem converter antes de chamar
  classes de negocio;
- DataTypes genericos ficam em `packages/datatype-*`;
- DataTypes especificos de produto devem ficar no projeto consumidor.

Documentacao:

- mudancas em API publica, comportamento documentavel, modulo Composer,
  DataType, contrato, atributo, fluxo HTTP, instalacao, configuracao,
  ambiente, observabilidade ou extensao devem atualizar documentacao no mesmo
  trabalho;
- `docs/ias/` e a documentacao para agentes/IAs;
- `docs/html/` e a documentacao visual para humanos;
- referencia publica e gerada por `php docs/generate-reference.php`.

## Ambiente e verificacoes

Ambiente oficial:

- Dev Container em `.devcontainer/`;
- Docker Compose de servicos em `.devcontainer/docker-compose.services.yml`;
- suite completa com `sh .devcontainer/check.sh` dentro do Dev Container.

Checks por escopo:

- pacote especifico: `composer check` ou `composer test` dentro do pacote;
- mudancas em core, contratos, lifecycle HTTP, attributes ou DataTypes exigem
  testes do pacote afetado;
- checks de `api/` so devem rodar quando `api/` for alterado.

## Estado conhecido de PRs e publicacao

Registros da memoria local indicam:

- foi criado workflow de subtree split para manifests Composer;
- houve uma sequencia antiga de PRs empilhados `#166` a `#175`;
- esses PRs foram fechados;
- PRs empilhados `#176` a `#185` foram recriados pelo usuario/agente
  `ScriptPlayerAgent`, usando heads do fork `ScriptPlayerAgent/Bifrost-API`;
- checks remotos `framework-check` desses PRs passaram;
- uma branch local `skeleton/estrutura-guia` permaneceu sem push;
- havia guias locais em `tmp/PUBLICAR-COMPOSER.md` e
  `tmp/PENDENCIAS-PUBLICACAO.md`.

Esse trecho deve ser revalidado no GitHub antes de qualquer acao nova, porque
representa memoria local e pode estar desatualizado.

## Pendencias e cuidados

Pendencias principais:

- confirmar definitivamente o nome publico final antes de publicar pacotes;
- renomear namespaces, pacotes, README e documentacao antes de tags e
  Packagist;
- decidir se todos os repositorios serao criados de uma vez ou em fases;
- validar se `PLANO_REPOSITORIOS_GITHUB.md` deve virar documentacao oficial ou
  permanecer como plano temporario;
- revalidar o estado dos PRs e branches antes de continuar a publicacao;
- testar instalacao real via Composer depois da separacao;
- garantir que projetos consumidores, como `dossier`, nao sejam tratados como
  parte do framework.

Cuidados:

- nao publicar nada com o nome antigo se a decisao final for `Elavora API`;
- nao misturar regra de produto no framework;
- nao mover DataTypes especificos de produto para os pacotes genericos;
- nao alterar testes existentes sem permissao explicita;
- nao fazer commit, push ou PR sem autorizacao explicita.

