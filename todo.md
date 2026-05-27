# Análise Arquitetural do Bifrost-API

> Atualização de escopo: o repositório `Felipe-Cavalca/Bifrost` integra
> `Bifrost-API`, `Bifrost-Database` e outros módulos por merge. Portanto,
> `api/` deve permanecer compatível enquanto `packages/` evolui como
> distribuição Composer aditiva. Uma substituição integral exige migração
> coordenada dos repositórios consumidores.

## Diagnóstico

Hoje o Bifrost-API é uma aplicação executável distribuída como se fosse uma biblioteca. Há uma base reutilizável, mas o runtime do framework depende de controllers, rotas, filesystem, configurações e infraestrutura incluídos no próprio pacote.

O ponto central do acoplamento é `api/Core/Request.php`: a classe atua simultaneamente como request HTTP, kernel, dispatcher, pipeline de attributes, error handler, observabilidade e serializador de resposta.

## Separação Atual

| Área atual | Classificação recomendada | Motivo |
| --- | --- | --- |
| `Core/Request.php`, `AppError.php` | Framework, após divisão de responsabilidades | Lifecycle HTTP e erros são infraestrutura do framework |
| `Class/HttpResponse.php`, `TextResponse.php` | Framework HTTP | Respostas reutilizáveis |
| `Attributes/` | Framework ou pacote `http-attributes` | Pipeline transversal reutilizável |
| `Interface/` | Framework/contracts | Pontos de extensão |
| `Enum/HttpStatusCode.php` | Framework HTTP | Contrato HTTP |
| `Enum/Routes.php` | App/skeleton | Declara rotas concretas: `login`, `logout`, `health`, `ping` |
| `Enum/Path.php` | Remover do design público | Codifica namespace e diretório fixos da aplicação |
| `Controller/index.php` | App/skeleton/exemplo | Controller real com endpoints concretos |
| `index.php` | App/skeleton | Front controller da aplicação consumidora |
| `Worker.php` | App/skeleton ou package queue-runner | Processo executável, não biblioteca |
| `Integration/Storage`, `Database`, `Cache`, `Queue` | Packages opcionais | Dependem de fornecedor e ambiente |
| `Docker/`, `nginx.conf`, `supervisord.conf` | Skeleton/deploy example | Infraestrutura da aplicação final |

Há ainda rotas `login` e `logout` em `api/Enum/Routes.php` sem controllers correspondentes no repositório, reforçando que o arquivo é configuração de aplicação incompleta, não core.

## Problemas Arquiteturais

1. **Framework conhece controllers da aplicação.**
   `api/Core/Request.php` valida arquivos dentro de `Controller/` e monta classes `Bifrost\Controller\...`. Uma aplicação consumidora não consegue usar naturalmente `App\Http\Controller`.

2. **Roteamento é configuração de produto embutida no core.**
   `api/Core/Get.php` depende diretamente de `Bifrost\Enum\Routes`. Rotas precisam ser registradas pela aplicação em uma coleção ou arquivo de configuração.

3. **`Request` não representa uma request.**
   Ela inicializa settings, emite header, resolve controller, executa attributes, captura exceções e serializa saída. O nome deveria corresponder a um objeto HTTP; a execução deveria ficar em um `HttpKernel`.

4. **Estado global e side effects dificultam testes e extensibilidade.**
   `Get` substitui `$_GET` por objeto; `Post` faz o mesmo com `$_POST`. `Settings` emite headers e altera `ini_set`. `HttpResponse::jsonSerialize()` altera status HTTP durante serialização.

5. **Boot acoplado ao pacote.**
   `api/index.php` chama um autoloader próprio e instancia diretamente `new Request()`. Não existe objeto `Application`, container, configuração injetável ou registro explícito de rotas/providers.

6. **Integrações não estão desacopladas no core.**
   `Cache` herda de Redis, `Database` herda de PDO e `Queue` herda de Redis. O core deveria depender dos contratos, com adapters escolhidos no bootstrap.

7. **Erro interno pode expor informação sensível.**
   Exceções inesperadas retornam `$erro->getMessage()` ao cliente em `Request`. Em produção, o payload deve ser genérico e o detalhe ficar no log.

8. **CORS e headers são política de aplicação hardcoded.**
   `Settings` força CORS e headers globais. Isso deve ser middleware/configuração do skeleton.

## Pontos Fortes

- Contratos já existem para response, attributes, storage, queue e database.
- O pipeline `before`/`after` de attributes é uma base útil para middleware/interceptors.
- `HttpResponse`, `TextResponse` e `HttpStatusCode` estabelecem uma API HTTP inicial clara.
- Integrações externas já estão agrupadas em `Integration/`.
- Há testes para lifecycle, observabilidade, attributes e integrações.
- Composer, PHPStan, PHPUnit e checks já estão previstos no pacote.

## Estrutura Ideal

```text
bifrost/
├── packages/
│   ├── framework/
│   │   ├── composer.json                 # bifrost/framework
│   │   ├── src/
│   │   │   ├── Application.php
│   │   │   ├── Contracts/
│   │   │   ├── Http/
│   │   │   │   ├── Request.php
│   │   │   │   ├── Response.php
│   │   │   │   ├── HttpKernel.php
│   │   │   │   └── ResponseEmitter.php
│   │   │   ├── Routing/
│   │   │   │   ├── Router.php
│   │   │   │   ├── Route.php
│   │   │   │   └── ControllerResolver.php
│   │   │   ├── Middleware/
│   │   │   ├── Exception/
│   │   │   ├── Config/
│   │   │   └── Support/
│   │   └── tests/
│   ├── cache-redis/                      # bifrost/cache-redis
│   ├── queue-redis/                      # bifrost/queue-redis
│   ├── database-pdo/                     # bifrost/database-pdo
│   ├── storage-local/                    # bifrost/storage-local
│   ├── storage-s3/                       # bifrost/storage-s3
│   └── logger-mongodb/                   # bifrost/logger-mongodb
└── skeleton/
    ├── composer.json                     # bifrost/skeleton, type project
    ├── app/Http/Controller/HealthController.php
    ├── bootstrap/app.php
    ├── config/{app,cors,logging,cache,queue}.php
    ├── public/index.php
    ├── routes/api.php
    ├── bin/console
    ├── docker/
    └── tests/
```

## Boot Recomendado

O entrypoint deve pertencer ao skeleton e ser mínimo:

```php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$response = $app->handle(Bifrost\Http\Request::fromGlobals());
$app->emit($response);
```

`bootstrap/app.php` deve criar `Application`, carregar config, registrar providers/adapters, middlewares e `routes/api.php`. O framework recebe dependências; não procura controllers em diretórios próprios.

## Request Lifecycle Ideal

```text
public/index.php
  -> Request::fromGlobals()
  -> Application / HttpKernel
  -> middleware global
  -> Router::match()
  -> ControllerResolver
  -> attributes ou middleware da rota
  -> controller action
  -> exception handler
  -> Response
  -> ResponseEmitter
```

Attributes atuais podem continuar compatíveis, mas executados por um componente dedicado (`AttributeMiddleware`), não por `Request`.

## Composer, Autoload e Namespaces

O `autoload` atual em `api/composer.json` mistura PSR-4 com `classmap` para compensar nomes de arquivos incompatíveis:

- `Controller/index.php` contém `Index`.
- `DataTypes/base64.php` contém `Base64`.
- `DataTypes/filePath.php` contém `FilePath`.
- `DataTypes/url.php` contém `Url`.

A recomendação é renomear para `Index.php`, `Base64.php`, `FilePath.php` e `Url.php`, migrar código do framework para `src/` e manter somente:

```json
{
  "autoload": {
    "psr-4": {
      "Bifrost\\": "src/"
    }
  }
}
```

`api/Core/Autoload.php` deve deixar de existir no pacote publicado. Aplicações modernas carregam apenas `vendor/autoload.php`; o fallback manual e o `lcfirst()` mascaram violações de PSR-4.

Namespaces sugeridos:

```text
Bifrost\Http\*
Bifrost\Routing\*
Bifrost\Contracts\*
Bifrost\Middleware\*
Bifrost\Exception\*
Bifrost\Cache\Contract\*
Bifrost\Queue\Contract\*
App\Http\Controller\*
```

## Distribuição Via Composer

- `bifrost/framework`: biblioteca principal, sem controller, entrypoint, Docker ou rotas reais.
- `bifrost/skeleton`: projeto instalável com `composer create-project bifrost/skeleton minha-api`.
- Packages opcionais: Redis, PDO, S3, MongoDB logging e worker.
- O skeleton depende de `bifrost/framework` e escolhe adapters conforme necessidade.
- Imagem Docker deve ser do skeleton/exemplo, não requisito do framework.

## Prioridade de Migração

1. Separar `Controller/index.php`, `Routes.php`, `index.php`, `Worker.php` e Docker em um skeleton.
2. Corrigir PSR-4 e remover `classmap`/autoload manual.
3. Introduzir `Application`, `HttpKernel`, `Router`, `ResponseEmitter` e request HTTP imutável.
4. Substituir `Path` e resolução fixa por rotas registradas e controller resolver configurável.
5. Extrair adapters Redis/PDO/S3/Mongo para packages opcionais.
6. Migrar headers, CORS, sessões e logging para middleware/config/providers.
7. Preservar compatibilidade temporária com attributes e respostas atuais.
