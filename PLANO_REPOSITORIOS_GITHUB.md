# Plano de repositorios GitHub e pacotes Composer

## Decisao recomendada

A organizacao GitHub deve ser `Elavora`.

Os produtos que usarem o framework terao seus proprios repositorios fora deste
plano. O `dossier` e apenas um exemplo de projeto consumidor.
O framework/API reutilizavel nao deve ser publicado como `bifrost`; esse nome
e provisiorio no codigo atual e deve ser substituido antes de subir para os
repositorios novos.

Use `api` como linha de pacotes da organizacao `Elavora`:

- Repositorios GitHub: `Elavora/api-*`
- Pacotes Composer: `elavora/api-*`
- Namespace PHP sugerido: `Elavora\Api`

Exemplo:

- repositorio: `Elavora/api-framework`
- pacote Composer: `elavora/api-framework`

## Regra de renomeacao antes do primeiro push

Antes de publicar os repositorios, substituir todas as ocorrencias antigas de:

- `bifrost/framework`
- `bifrost/skeleton`
- `bifrost/*`
- `Bifrost\...`
- `Bifrost Framework`
- `Bifrost`
- `bifrost`

Por:

- `elavora/api-framework`
- `elavora/api-skeleton`
- `elavora/api-*`
- `Elavora\Api\...`
- `Elavora API`
- `api`

Essa troca deve acontecer antes de taggear versoes e antes de publicar no
Packagist, para evitar pacote legado com nome errado.

## Repositorios dos modulos do framework/API

Como voce quer criar um repositorio por modulo, use este padrao:

- Repositorio GitHub: `Elavora/api-<modulo>`
- Pacote Composer: `elavora/api-<modulo>`
- Namespace PHP: `Elavora\Api`

Repositorios a criar:

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

## Repositorios que eu criaria primeiro

Nao precisa criar todos os modulos no primeiro dia. Comece pelos pacotes que
desbloqueiam instalacao real:

1. `Elavora/api-framework`
2. `Elavora/api-skeleton`
3. `Elavora/api-datatype-core`
4. `Elavora/api-datatypes`
5. `Elavora/api-database-pdo`
6. `Elavora/api-database-mysql`
7. `Elavora/api-database-postgresql`
8. `Elavora/api-cache-apcu`
9. `Elavora/api-redis`
10. `Elavora/api-cache-redis`
11. `Elavora/api-queue-redis`
12. `Elavora/api-queue-worker`
13. `Elavora/api-storage-local`
14. `Elavora/api-storage-s3`
15. `Elavora/api-log-stdout`
16. `Elavora/api-log-file`
17. `Elavora/api-log-mongodb`

Os DataTypes individuais podem entrar depois, conforme necessidade real.

## Sequencia para criar os repositorios dos modulos

1. Escolher o nome final do framework.
2. Escolher o vendor Composer.
3. Fazer replace local no projeto atual.
4. Rodar a suite completa do projeto renomeado.
5. Criar os repositorios de modulos na organizacao `Elavora`.
6. Separar cada pasta de pacote em seu repositorio.
7. Ajustar `composer.json`, README, namespace e CI de cada repositorio.
8. Publicar primeiro `framework`.
9. Publicar depois `skeleton`.
10. Publicar extensoes conforme dependencia.

## Sequencia para Composer/Packagist

1. Criar ou usar uma conta Packagist com permissao para `elavora`.
2. Comecar publicando apenas:
   - `elavora/api-framework`
   - `elavora/api-skeleton`
3. Publicar extensoes depois, conforme estabilidade:
   - cache;
   - redis;
   - queue;
   - database;
   - storage;
   - logs;
   - datatypes.
4. Taggear versoes com SemVer:
   - `v0.1.0` para primeira versao experimental;
   - `v1.0.0` apenas quando API publica estiver estavel.

## Ordem pratica recomendada

1. Definir o nome final.
2. Fazer replace de `bifrost`/`Bifrost` no projeto atual.
3. Validar localmente `sh .devcontainer/check.sh`.
4. Separar primeiro `packages/framework` em `Elavora/api-framework`.
5. Separar depois `skeleton` em `Elavora/api-skeleton`.
6. Publicar `elavora/api-framework`.
7. Publicar `elavora/api-skeleton`.
8. Testar:

```bash
composer create-project elavora/api-skeleton api
```

9. Validar o uso em um projeto consumidor, como o `dossier`, sem publicar esse
   projeto como parte do framework.

## Observacoes importantes

- Nao misture codigo especifico de projetos consumidores dentro do framework.
- DataTypes genericos ficam em `elavora/api-datatype-*`.
- DataTypes do produto, como regras especificas de documentos, usuarios ou
  organizacoes, devem ficar no projeto consumidor.
- O framework pode ser publico mesmo que projetos consumidores sejam privados.
- O nome antigo `Bifrost` nao deve aparecer em pacotes, namespaces,
  documentacao publica, tags ou README depois da renomeacao.
