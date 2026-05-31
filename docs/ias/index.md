# Bifrost para IAs

## Objetivo

Esta pasta ensina agentes e IAs a trabalhar na versao nova do Bifrost.

## Escopo

O foco e criar e manter projetos novos usando Composer, `bifrost/framework`,
`bifrost/skeleton`, DataTypes e extensoes opcionais.

## Regras obrigatorias

- Projeto novo cria backend dentro de `api/` usando `bifrost/skeleton`.
- Core fica em `packages/framework`.
- DataTypes ficam em `packages/datatypes`.
- Infra opcional fica em extensoes Composer.
- Sempre atualize `docs/human` e `docs/ias` quando mudar comportamento publico.

## Mapa

- `project-setup.md`: como um projeto novo nasce.
- `composer-reference.md`: referencia operacional da distribuicao Composer.
- `framework-map.md`: o que existe no framework.
- `packages.md`: pacotes Composer disponiveis.
- `extension-rules.md`: como criar extensoes.
- `coding-rules.md`: regras de codigo.
- `validation-checklist.md`: checks antes de finalizar.
