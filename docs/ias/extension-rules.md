# Regras de Extensoes

## Objetivo

Padronizar novos modulos opcionais do Bifrost.

## Quando criar extensao

Crie pacote separado quando a feature:

- usa servico externo;
- exige extensao PHP;
- exige SDK de fornecedor;
- precisa de configuracao propria;
- pode ser removida sem quebrar o core.

## Estrutura minima

```text
packages/nome-do-pacote/
|-- src/
|-- tests/
|-- composer.json
|-- phpunit.xml
`-- README.md
```

## Regras

- Extensao implementa `Bifrost\Framework\Contracts\Extension`.
- Extensao registra servicos no container.
- Core nao pode depender da extensao.
