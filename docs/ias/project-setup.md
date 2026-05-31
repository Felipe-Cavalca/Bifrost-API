# Projeto Novo

## Objetivo

Orientar a criacao de um backend Bifrost do zero.

## Estrutura esperada

```text
meu-sistema/
|-- api/
|   |-- app/
|   |-- core/
|   |   |-- bootstrap/
|   |   |-- compose/
|   |   |-- config/
|   |   `-- routes/
|   |-- public/
|   |-- tests/
|   `-- composer.json
|-- database/
`-- app/
```

## Comandos

```bash
mkdir api
cd api
composer create-project bifrost/skeleton .
cp .env.example .env
docker compose up --build
```

## Regras para IAs

- Nao use `classmap`.
- Namespace da aplicacao e `App\`.
- Entrypoint HTTP e `public/index.php`.
- Boot da aplicacao e `core/bootstrap/app.php`.
- Extensoes opcionais ficam em `core/config/extensions.php`.
- Aliases e URLs fora da convencao `/controller/action` ficam em
  `app/Http/HttpRoutes.php`.
- Overlays Docker Compose ficam em `core/compose/`.
- Ative overlays com multiplos argumentos `-f`; nao copie seu conteudo para o
  `docker-compose.yml` principal.
- Codigo do app segue as pastas `Attributes`, `Contracts`, `DataTypes`,
  `Enums`, `Http/Controller`, `Integrations`, `Repositories`, `Services`,
  `Support` e `Worker`.
