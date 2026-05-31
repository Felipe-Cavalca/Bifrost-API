# bifrost/datatypes

Pacote agregador dos DataTypes Bifrost.

## Uso

Instale apenas o DataType que sua aplicacao usa:

```bash
composer require bifrost/datatype-email
composer require bifrost/datatype-cpf
```

Se quiser todos os DataTypes mantidos pelo Bifrost, use o agregador:

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
