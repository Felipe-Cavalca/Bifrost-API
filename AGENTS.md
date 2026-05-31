# AGENTS.md local

## Convencoes do projeto

- Antes de nomear branch, arquivo, classe, endpoint, tabela, coluna ou variavel de ambiente, consulte `docs/ias/coding-rules.md`.
- Cada tarefa diferente deve usar uma branch diferente.
- Branches devem seguir o formato `module/function`.
- Antes de abrir ou editar PR, consulte `.github/PULL_REQUEST_TEMPLATE.md`.
- Use portugues do Brasil em mensagens finais, commits, PRs e documentacao.
- Nao faca commit, push ou PR sem autorizacao explicita.
- Preserve o padrao existente do modulo alterado.
- Antes de alterar core, integracoes, DataTypes, variaveis de ambiente, observabilidade ou fluxo HTTP, consulte `docs/ias/framework-map.md` e `docs/ias/packages.md`.

## Direcao arquitetural

- O alvo novo e distribuicao por Composer, com `packages/` e `skeleton/`.
- A documentacao deve falar apenas da versao nova.
- Projeto novo cria backend dentro de `api/` usando `bifrost/skeleton`.
- Implementacoes novas vivem em `packages/` e `skeleton/`.

## Areas do repositorio

- `packages/framework/`: nucleo Composer do framework.
- `packages/*/`: extensoes Composer opcionais.
- `packages/datatypes/`: DataTypes reutilizaveis para tipagem forte e validacao.
- `skeleton/`: projeto base publicado como `bifrost/skeleton`.
- `docs/human/`: documentacao visual para humanos.
- `docs/ias/`: documentacao objetiva para agentes e IAs.

## Ambiente

- Use Docker, Docker Compose ou Dev Container para comandos do projeto sempre que possivel.
- Nao instale dependencias na maquina local; instale apenas dentro de container.
- Dentro de containers do projeto, instalacoes e comandos necessarios para a tarefa estao liberados.
- O ambiente modular oficial e `docker/modular/docker-compose.test.yml`.
- O Docker em `api/Docker/` e referencia antiga; nao use como modelo do Composer novo.

## Codigo orientado a agentes

- Prefira arquivos pequenos e coesos.
- Prefira funcoes curtas, com uma responsabilidade clara e nomes especificos.
- Evite aninhamento profundo; use retornos antecipados quando isso simplificar a leitura.
- Use nomes pesquisaveis e alinhados ao dominio.
- Antes de criar abstracao nova, procure duplicacao real com `rg`.
- Nao duplique regra de negocio; extraia para classe, funcao ou DataType apropriado.
- Comentarios devem explicar decisoes, restricoes, excecoes ou contexto nao obvio.
- Mensagens de erro devem ter contexto suficiente para debug sem expor dados sensiveis.
- Melhorias de core nao devem introduzir regra de produto no framework.

## DataTypes

- Use DataTypes do dominio em DTOs, services e regras de negocio.
- Crie DataType quando o valor tiver validacao propria, aparecer em mais de um fluxo ou representar conceito do dominio.
- Nao crie DataType apenas para embrulhar primitivo sem regra.
- Controllers podem receber dados brutos da request, mas devem converte-los antes de chamar classes de negocio.
- DataTypes genericos ficam em `packages/datatypes/`.
- DataTypes especificos de infraestrutura ou fornecedor devem ficar no pacote da extensao correspondente.

## Dependencias

- Injete dependencias por construtor ou parametro quando a classe tiver regra de negocio testavel.
- Nao instancie integracoes externas diretamente dentro do dominio.
- Encapsule fornecedores externos atras de interfaces ou adapters do projeto.
- Use fakes nomeados em testes quando precisar substituir I/O externo.
- Dependencias opcionais nao entram em `bifrost/framework`; elas ficam em pacotes separados.

## Git e pull requests

- PRs devem receber labels existentes em `.github/labels.yml`.
- Antes de criar ou atualizar labels de um PR, consulte `.github/labels.yml`.
- PRs de documentacao devem usar a label `documentation`.
- PRs feitos por agente devem usar tambem a label `IA`, quando a ferramenta permitir aplicar labels.
- Commits feitos por agente devem usar:
  - Nome: `ScriptPlayerAgent`
  - E-mail: `scriptPlayerAgent@felipecavalca.dev`

## Verificacoes

- Monorepo modular: `docker compose -f docker/modular/docker-compose.test.yml run --rm --build tests`.
- Pacote especifico: rode `composer check` ou `composer test` dentro do pacote alterado.
- Mudancas em core, contratos, lifecycle HTTP, attributes ou DataTypes exigem testes no pacote afetado.
- Rode checks de `api/` apenas quando alterar `api/`.

## Documentacao

- Mudancas de padrao do projeto devem atualizar `docs/ias/coding-rules.md`.
- Mudancas de arquitetura, core, observabilidade, ambiente ou contratos publicos devem atualizar `docs/ias/framework-map.md`.
- Mudancas de distribuicao Composer devem atualizar `docs/ias/packages.md`.
- Guias para humanos ficam em `docs/human/`.
- Guias para IAs/agentes ficam em `docs/ias/`.

## Escopo

- Nao misture mudanca funcional com mudanca arquitetural ampla sem registrar a decisao.
- Se uma feature nova nao tiver pacote correto, crie ou proponha o pacote antes de acoplar no framework.
