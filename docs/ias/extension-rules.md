# Regras de Extensoes

## Objetivo

Padronizar extensoes opcionais registradas no boot do Bifrost.

## Quando criar extensao

Crie uma extensao de boot em pacote separado quando a feature:

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

Pacotes que nao participam do boot, como DataTypes e `bifrost/queue-worker`,
continuam opcionais, mas nao precisam implementar `Extension`.
