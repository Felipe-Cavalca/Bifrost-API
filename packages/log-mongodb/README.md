# bifrost/log-mongodb

Adapter opcional de persistencia de logs em MongoDB para o Bifrost Framework.
O pacote registra o contrato local
`Bifrost\Extension\LogMongoDb\Contracts\LogWriter`, que recebe documentos de
log prontos e os grava na colecao configurada.

```php
use Bifrost\Extension\LogMongoDb\Contracts\LogWriter;
use Bifrost\Extension\LogMongoDb\MongoLogExtension;

$application->extend(new MongoLogExtension([
    'uri' => 'mongodb://mongo:27017',
    'database' => 'bifrost_logs',
    'collection' => 'application_logs',
]));

$application->container()->get(LogWriter::class)->write([
    'timestamp' => gmdate('c'),
    'level' => 'info',
    'message' => 'Requisicao recebida',
    'request_id' => $requestId,
    'context' => [],
]);
```

Tambem podem ser informados `host`, `port`, `username` e `password` no lugar
de `uri`. A porta padrao e `27017` e a colecao padrao e `logs`.

## Compatibilidade

| API existente | Comportamento preservado no pacote |
| --- | --- |
| `Bifrost\Integration\Database\MongoDatabase` | Configuracao aceita `uri` ou `host`, `port`, `username`, `password` e `database`; a escrita continua sendo um documento em uma colecao MongoDB. |
| `Bifrost\Core\Logger` | O documento com `timestamp`, `level`, `message`, `request_id` e `context` pode ser gravado sem transformacao por `LogWriter::write()`. |
| `Bifrost\Interface\NoSqlDatabase` | Permanece contrato do runtime legado; este pacote nao o substitui nem exige alteracao nele. |

Os valores produzidos por `Settings::getSettingsMongo()` podem ser passados
diretamente a `MongoLogConfig::fromArray()`. O valor legado de
`BFR_API_LOG_COLLECTION` corresponde a opcao `collection` da extensao.

O framework modular ainda nao publica um contrato comum de logging ou de
log sink. Por isso, `LogWriter` pertence somente a este pacote e o registro da
extensao e aditivo; integrar diferentes providers sob o mesmo contrato exige
primeiro definir esse contrato no framework.
