# Convencoes

## Idioma e Nomes

- Use portugues do Brasil em documentacao, commits e PRs.
- Use ingles em codigo, pacotes, branches, endpoints e variaveis de ambiente.
- Use nomes tecnicos ASCII, claros e pesquisaveis.
- Classes e segmentos de namespace usam `PascalCase`.
- Metodos e variaveis PHP usam `camelCase`.
- Variaveis de ambiente usam `UPPER_SNAKE_CASE`.

## Branches e Commits

Branches usam `module/function`, com segmentos em `kebab-case`:

```text
framework/modular-packages
docs/distribution-guide
ci/package-validation
```

Commits usam Conventional Commits em PT-BR:

```text
feat: adiciona extensao redis
build: prepara distribuicao composer
docs: documenta publicacao dos pacotes
ci: valida pacotes modulares
```

## Estrutura PHP

- Codigo do nucleo fica em `packages/framework/src/`.
- Integracoes ficam em `packages/<package>/src/`.
- Codigo existente compativel com o agregador permanece em `api/` ate
  migracao coordenada.
- Codigo da aplicacao inicial fica em `skeleton/app/`.
- O entrypoint da aplicacao fica em `skeleton/public/index.php`.
- Configuracao de extensoes da aplicacao fica em `skeleton/config/`.
- Testes ficam em `<package>/tests/` ou `skeleton/tests/`.

Namespaces:

```text
packages/framework/src/Http/Request.php       Bifrost\Framework\Http\Request
packages/cache-redis/src/RedisCache.php        Bifrost\Extension\CacheRedis\RedisCache
skeleton/app/Http/Controller/HealthController.php App\Http\Controller\HealthController
api/Core/Request.php                           Bifrost\Core\Request
```

## Composer

- Nomes de pacotes seguem `bifrost/<package>`.
- Codigo fonte usa autoload PSR-4.
- `classmap` e permitido apenas para compatibilidade externa comprovada, nao
  para classes do framework.
- O nucleo nao requer dependencias opcionais de banco, Redis ou fila.
- Cada extensao declara as extensoes PHP e pacotes Composer que utiliza.
- Mudancas de API publica devem respeitar SemVer.
- Pacotes novos nao removem namespaces `Bifrost\*` existentes em `api/` sem
  plano coordenado com `Felipe-Cavalca/Bifrost`.

## Extensoes e Dependencias

- Integracoes externas dependem dos contratos de `bifrost/framework`.
- Uma extensao deve registrar implementacoes pelo container da aplicacao.
- Nao instancie clientes de fornecedores no core.
- O skeleton pode ativar somente extensoes efetivamente instaladas.

## HTTP

- Endpoints usam recursos em plural ou endpoints tecnicos claros, como
  `GET /health`.
- Rotas e controllers pertencem a aplicacao, nao ao pacote `framework`.
- Controllers recebem `Request` e retornam `Response`, arrays ou texto.
- Middleware recebe a requisicao e a proxima etapa do pipeline.

## Testes e Documentacao

- Novas funcoes e novos comportamentos devem ter testes correspondentes.
- Core, contratos e integracoes exigem execucao do ambiente modular completo.
- Mudancas de arquitetura devem atualizar `docs/architecture.md`.
- Mudancas de distribuicao ou versao devem atualizar `docs/releasing.md`.
- Mudancas no runtime compativel continuam seguindo `api/composer.json`.
