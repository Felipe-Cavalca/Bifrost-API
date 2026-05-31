# Checklist de Validacao

Antes de finalizar:

- `git status --short`
- pacote alterado: `composer check` ou `composer test`
- core alterado: teste de `packages/framework`
- DataType alterado: teste do pacote `packages/datatype-*` especifico
- agregador de DataTypes alterado: teste de `packages/datatypes`
- extensao alterada: teste do pacote especifico
- skeleton alterado: teste de `skeleton`
- mudanca central: `sh .devcontainer/check.sh` dentro do Dev Container

Depois de subir os servicos auxiliares do Dev Container, remova os volumes
somente quando a perda dos dados locais for intencional:

```bash
docker compose -f .devcontainer/docker-compose.services.yml down --volumes
```
