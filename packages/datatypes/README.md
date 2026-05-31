# bifrost/datatypes

DataTypes reutilizaveis para aplicacoes Bifrost.

## Uso

```bash
composer require bifrost/datatypes
```

```php
use Bifrost\DataTypes\Brazil\Cpf;
use Bifrost\DataTypes\Email;

$cpf = Cpf::from('529.982.247-25');
$email = Email::from('team@bifrost.dev');
```

Todos os tipos implementam `Bifrost\Framework\Contracts\DataType`, por isso
podem ser usados em attributes do framework:

```php
#[RequiredFields(['email' => Email::class, 'cpf' => Cpf::class])]
```
