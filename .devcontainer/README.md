# Dev Container

Este e o ambiente oficial para desenvolver e validar o monorepo Composer sem
instalar PHP, Composer, bancos ou extensoes diretamente na maquina local.

## Pre-requisitos

- Docker;
- Visual Studio Code;
- extensao Dev Containers do Visual Studio Code.

Ao abrir o repositorio no Dev Container, o VS Code:

1. cria a imagem PHP de desenvolvimento;
2. monta o repositorio dentro do container;
3. conecta o container a rede `bifrost-devcontainer`;
4. sobe Redis, MySQL e PostgreSQL como servicos auxiliares.

## Validacao completa

Execute dentro do terminal do Dev Container:

```bash
sh .devcontainer/check.sh
```

O script valida o framework, os pacotes opcionais, os DataTypes e o skeleton
usando repositorios Composer locais do tipo `path`.

## Servicos auxiliares

Para subir ou encerrar os servicos manualmente na maquina local:

```bash
docker compose -f .devcontainer/docker-compose.services.yml up -d
docker compose -f .devcontainer/docker-compose.services.yml down --volumes
```
