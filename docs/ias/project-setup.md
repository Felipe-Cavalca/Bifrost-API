# Projeto Novo

## Objetivo

Orientar a criacao de um backend Bifrost do zero.

## Estrutura esperada

```text
meu-sistema/
|-- api/
|   |-- app/
|   |-- bootstrap/
|   |-- config/
|   |-- public/
|   |-- routes/
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
- Boot da aplicacao e `bootstrap/app.php`.
- Rotas ficam em `routes/api.php`.
