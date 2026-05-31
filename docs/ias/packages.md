# Pacotes Composer

| Pacote | Uso |
| --- | --- |
| `bifrost/framework` | Core HTTP |
| `bifrost/skeleton` | Projeto inicial |
| `bifrost/datatype-core` | Base para DataTypes |
| `bifrost/datatype-email` | DataType Email |
| `bifrost/datatype-url` | DataType Url |
| `bifrost/datatype-base64` | DataType Base64 |
| `bifrost/datatype-json` | DataType Json |
| `bifrost/datatype-uuid` | DataType Uuid |
| `bifrost/datatype-date-time` | DataType DateTime |
| `bifrost/datatype-cpf` | DataType CPF |
| `bifrost/datatype-cnpj` | DataType CNPJ |
| `bifrost/datatype-file-name` | DataType FileName |
| `bifrost/datatype-folder-name` | DataType FolderName |
| `bifrost/datatype-file-path` | DataType FilePath |
| `bifrost/datatype-folder-path` | DataType FolderPath |
| `bifrost/datatype-storage-key` | DataType StorageKey |
| `bifrost/datatypes` | Agregador com todos os DataTypes |
| `bifrost/cache-apcu` | Cache local APCu |
| `bifrost/redis` | Cliente Redis encapsulado e reutilizavel para extensoes |
| `bifrost/cache-redis` | Cache Redis |
| `bifrost/queue-redis` | Fila Redis |
| `bifrost/queue-worker` | Worker para consumo de filas |
| `bifrost/database-pdo` | Base PDO |
| `bifrost/database-mysql` | MySQL |
| `bifrost/database-postgresql` | PostgreSQL |
| `bifrost/database-sqlite` | SQLite |
| `bifrost/storage-local` | Storage local |
| `bifrost/storage-s3` | Storage S3 |
| `bifrost/log-stdout` | Logs em stdout/stderr |
| `bifrost/log-file` | Logs em arquivo |
| `bifrost/log-mongodb` | Logs em MongoDB |

## Regra

Instale apenas o pacote necessario para a feature usada pelo projeto.

O overlay atual `core/compose/redis.yml` configura `CACHE_DRIVER=redis`.
Portanto, ao usar esse overlay, instale `bifrost/cache-redis`. O pacote
`bifrost/queue-redis` pode compartilhar o mesmo Redis quando tambem estiver
instalado. Um perfil Docker para fila Redis isolada ainda nao existe no
skeleton.

O pacote `bifrost/database-sqlite` nao e selecionado automaticamente por
`DB_DRIVER`; registre sua extensao explicitamente no boot da aplicacao.

## Estrutura do skeleton

O projeto criado por `bifrost/skeleton` separa:

- `core/bootstrap/`, `core/config/` e `core/compose/` para infraestrutura
  basica do projeto;
- `app/Http/HttpRoutes.php` para aliases e URLs fora da convencao
  `/controller/action`;
- `public/` como unica pasta exposta pelo servidor HTTP;
- `app/` para codigo do produto;
- `tests/` para testes da aplicacao.

Dentro de `app/`, use as convencoes `Attributes`, `Contracts`, `DataTypes`,
`Enums`, `Http/Controller`, `Integrations`, `Repositories`, `Services`,
`Support` e `Worker`.
