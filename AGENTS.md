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

## Areas do repositorio

- `packages/framework/`: nucleo Composer do framework.
- `packages/*/`: extensoes Composer opcionais.
- `packages/datatype-*/`: DataTypes modulares para tipagem forte e validacao.
- `packages/datatypes/`: pacote agregador dos DataTypes.
- `skeleton/`: projeto base publicado como `bifrost/skeleton`.
- `docs/human/`: documentacao visual para humanos.
- `docs/ias/`: documentacao objetiva para agentes e IAs.

## Ambiente

- Use Docker, Docker Compose ou Dev Container para comandos do projeto sempre que possivel.
- Nao instale dependencias na maquina local; instale apenas dentro de container.
- Dentro de containers do projeto, instalacoes e comandos necessarios para a tarefa estao liberados.
- O ambiente modular oficial e o Dev Container em `.devcontainer/`.
- Execute a suite completa diretamente dentro do Dev Container com
  `sh .devcontainer/check.sh`.
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
- DataTypes genericos ficam em pacotes `packages/datatype-*/`, um pacote por tipo ou grupo pequeno.
- `packages/datatypes/` deve apenas agregar os DataTypes publicados.
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

- Monorepo modular: `sh .devcontainer/check.sh` dentro do Dev Container.
- Pacote especifico: rode `composer check` ou `composer test` dentro do pacote alterado.
- Mudancas em core, contratos, lifecycle HTTP, attributes ou DataTypes exigem testes no pacote afetado.
- Rode checks de `api/` apenas quando alterar `api/`.

## Documentacao

- Toda mudanca em API publica, comportamento documentavel, modulo Composer, DataType, contrato, atributo, fluxo HTTP, instalacao, configuracao, ambiente, observabilidade ou extensao deve atualizar a documentacao correspondente no mesmo trabalho.
- Mudancas de padrao do projeto devem atualizar `docs/ias/coding-rules.md`.
- Mudancas de arquitetura, core, observabilidade, ambiente ou contratos publicos devem atualizar `docs/ias/framework-map.md`.
- Mudancas de distribuicao Composer devem atualizar `docs/ias/packages.md`.
- Mudancas que afetam como usuarios usam o framework devem atualizar os guias humanos em `docs/human/`.
- Mudancas em classes, metodos, parametros, retornos ou PHPDoc de API publica devem regenerar a referencia humana com `tools/generate-human-reference.php`.
- Guias para IAs/agentes ficam em `docs/ias/`.
- Se uma mudanca nao exigir documentacao, registre objetivamente o motivo no resumo final.

## Memoria local para agentes

- Use o SQLite em `tmp/agent-memory/bifrost-agent-memory.sqlite` como memoria local auxiliar do projeto.
- Antes de iniciar uma tarefa, consulte a memoria quando o pedido depender de decisoes anteriores, preferencias do usuario, pendencias ou contexto arquitetural.
- Ao finalizar uma tarefa relevante, registre um resumo curto na memoria com decisoes, preferencias novas, pendencias e verificacoes executadas.
- A memoria e apenas apoio: o codigo e a documentacao do repositorio continuam sendo a fonte final da verdade.
- Nao grave segredos, tokens, credenciais, valores de `.env`, dados pessoais sensiveis ou logs extensos na memoria.
- Registros devem ser objetivos, em portugues do Brasil, com referencias a arquivos quando util.
- Se a memoria conflitar com o codigo atual, siga o codigo e atualize a memoria com a decisao corrigida.

## Escopo

- Nao misture mudanca funcional com mudanca arquitetural ampla sem registrar a decisao.
- Se uma feature nova nao tiver pacote correto, crie ou proponha o pacote antes de acoplar no framework.
