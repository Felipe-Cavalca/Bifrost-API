# bifrost/storage-s3

Armazenamento S3 opcional para o Bifrost Framework.

Registre `S3StorageExtension` com `bucket` e as configuracoes aceitas pelo
`Aws\S3\S3Client`, como `region`, `version`, `credentials`, `endpoint` e
`use_path_style_endpoint`.

```php
$application->extend(new S3StorageExtension([
    'bucket' => 'uploads',
    'region' => 'us-east-1',
    'version' => 'latest',
]));
$storage = $application->container()->get(Storage::class);

$url = $storage->temporaryUrl('reports/example.txt');
```

Este pacote e aditivo. Ele nao substitui `Bifrost\Interface\Storage`,
`Bifrost\Integration\Storage\S3Storage` ou o alias legado
`Bifrost\Integration\S3Storage` do modulo `bifrost/api`.
