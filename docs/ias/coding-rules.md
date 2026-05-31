# Regras de Codigo

## PHP

- Use `declare(strict_types=1)`.
- Use PSR-4.
- Use classes pequenas e coesas.
- Evite helpers globais.
- Prefira injeção de dependência.

## Controllers

- Recebem `Request`.
- Retornam `Response`, array ou string.
- Devem converter dados brutos em DTOs/DataTypes antes de chamar regra de negocio.

## DataTypes

- Implementam `Bifrost\Framework\Contracts\DataType`.
- Devem validar entrada.
- Devem expor `value()`.
- Devem ser serializaveis quando fizer sentido.

## Attributes

- Validam contrato HTTP.
- Nao carregam regra de negocio do produto.
