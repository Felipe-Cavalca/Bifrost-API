# Docker

Esta pasta contem o ambiente Docker usado para validar localmente a distribuicao
modular do Bifrost por Composer.

Ela nao e copiada para projetos criados com `bifrost/skeleton`. O objetivo e
testar o monorepo antes da publicacao dos pacotes.

## Ambiente modular

Os arquivos ficam em `docker/modular/`:

| Arquivo | Finalidade |
| --- | --- |
| `docker-compose.test.yml` | Sobe Redis, MySQL, PostgreSQL e o container de testes. |
| `Dockerfile.test` | Cria a imagem PHP 8.3 com Composer e extensoes exigidas pela suite. |
| `Dockerfile.test.dockerignore` | Reduz o contexto enviado ao build modular. |
| `check.sh` | Valida os pacotes Composer, instala dependencias locais e executa os testes. |

O container `tests` executa o `check.sh`. Os demais containers existem para os
testes de integracao que dependem de servicos externos.

## Executar a validacao completa

Na raiz do repositorio:

```bash
docker compose -f docker/modular/docker-compose.test.yml run --rm --build tests
```

Esse comando valida:

- o core em `packages/framework/`;
- cada extensao em `packages/*/`;
- cada DataType modular;
- o agregador `bifrost/datatypes`;
- o projeto inicial em `skeleton/`.

Durante a execucao, o `check.sh` registra os pacotes locais como repositorios
Composer do tipo `path`. Isso simula a instalacao entre pacotes antes que eles
sejam publicados.
