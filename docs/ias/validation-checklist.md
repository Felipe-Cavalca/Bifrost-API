# Checklist de Validacao

Antes de finalizar:

- `git status --short`
- pacote alterado: `composer check` ou `composer test`
- core alterado: teste de `packages/framework`
- DataTypes alterado: teste de `packages/datatypes`
- extensao alterada: teste do pacote especifico
- skeleton alterado: teste de `skeleton`
- mudanca central: `sh .devcontainer/check.sh` dentro do Dev Container

Depois de rodar Docker:

```bash
docker compose -f .devcontainer/docker-compose.services.yml down --volumes
```
