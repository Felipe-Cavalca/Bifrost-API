# Checklist de Validacao

Antes de finalizar:

- `git status --short`
- pacote alterado: `composer check` ou `composer test`
- core alterado: teste de `packages/framework`
- DataTypes alterado: teste de `packages/datatypes`
- extensao alterada: teste do pacote especifico
- skeleton alterado: teste de `skeleton`
- mudanca central: `docker compose -f docker/modular/docker-compose.test.yml run --rm --build tests`

Depois de rodar Docker:

```bash
docker compose -f docker/modular/docker-compose.test.yml down --volumes
```
