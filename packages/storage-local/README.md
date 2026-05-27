# bifrost/storage-local

Armazenamento local opcional para o Bifrost Framework.

Registre `LocalStorageExtension` informando o diretorio raiz onde os objetos
serao gravados. O servico registrado implementa
`Bifrost\Framework\Contracts\Storage`.

```php
$application->extend(new LocalStorageExtension('/var/app/storage'));
$storage = $application->container()->get(Storage::class);

$storage->put('reports/example.txt', 'conteudo');
```

Este pacote e aditivo. Ele nao substitui `Bifrost\Interface\Storage` nem as
classes de storage existentes no modulo legado `bifrost/api`.
