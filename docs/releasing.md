# Publicacao e Versionamento

## Modelo de Distribuicao

Este repositorio e o monorepo de desenvolvimento. Cada diretorio publicavel
deve ser exposto como repositorio Composer independente por split:

| Diretorio | Pacote Composer |
| --- | --- |
| `packages/framework` | `bifrost/framework` |
| `packages/cache-apcu` | `bifrost/cache-apcu` |
| `packages/cache-redis` | `bifrost/cache-redis` |
| `packages/queue-redis` | `bifrost/queue-redis` |
| `packages/database-pdo` | `bifrost/database-pdo` |
| `packages/database-mysql` | `bifrost/database-mysql` |
| `packages/database-postgresql` | `bifrost/database-postgresql` |
| `packages/log-mongodb` | `bifrost/log-mongodb` |
| `packages/storage-local` | `bifrost/storage-local` |
| `packages/storage-s3` | `bifrost/storage-s3` |
| `skeleton` | `bifrost/skeleton` |

Packagist deve apontar para os repositorios resultantes do split, porque cada
pacote precisa expor seu `composer.json` na raiz do repositorio publicado.

O diretorio `api/` permanece versionado pelo fluxo atual do `Bifrost-API`,
pois e consumido pelo repositorio agregador `Felipe-Cavalca/Bifrost`.
Publicar pacotes Composer nao autoriza remover contratos legados sem release
coordenado.

## SemVer

- `MAJOR`: quebra em contrato publico, namespace, bootstrap ou configuracao.
- `MINOR`: nova extensao ou nova API compativel.
- `PATCH`: correcao sem quebra de contrato.

Pacotes podem evoluir independentemente. Extensoes declaram a faixa
compativel do framework em seus `composer.json`.

## Fluxo de Release

1. Execute a verificacao modular completa.
2. Defina quais pacotes mudaram desde a ultima publicacao.
3. Gere um split para cada diretorio alterado em seu repositorio publicavel.
4. Crie tag SemVer no repositorio de cada pacote alterado.
5. Atualize ou dispare a leitura do pacote no Packagist.
6. Valide a instalacao com `composer create-project bifrost/skeleton`.

O monorepo nao cria tags globais automaticas para evitar publicar uma versao
de extensao que nao mudou.

## Dependencias Entre Pacotes

- `bifrost/framework` nao depende de extensoes.
- `bifrost/cache-apcu`, `bifrost/cache-redis` e `bifrost/queue-redis` dependem somente do framework.
- `bifrost/database-pdo` depende somente do framework.
- Drivers de banco dependem de `bifrost/database-pdo` e do framework.
- `bifrost/log-mongodb` depende somente do framework enquanto mantiver contrato local.
- `bifrost/storage-local` depende somente do framework.
- `bifrost/storage-s3` depende do framework e do AWS SDK for PHP.
- `bifrost/skeleton` depende somente de `bifrost/framework`.
