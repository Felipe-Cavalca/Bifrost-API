# AGENTS.md local

## Convenções do projeto

- Antes de nomear branch, arquivo, classe, endpoint, tabela, coluna ou variável de ambiente, consulte `docs/conventions.md`.
- Cada tarefa diferente deve usar uma branch diferente.
- Branches devem seguir o formato `module/function`, conforme `docs/conventions.md`.
- Antes de abrir ou editar PR, consulte `.github/PULL_REQUEST_TEMPLATE.md`.
- Use português do Brasil em mensagens finais, commits, PRs e documentação.
- Não faça commit, push ou PR sem autorização explícita.
- Preserve o padrão existente do módulo alterado.
- Se uma instrução do usuário contrariar ou divergir de `AGENTS.md` ou da documentação em `docs/`, questione antes de executar e explique objetivamente o conflito.

## Ambiente

- Use Docker, Docker Compose ou Dev Container para comandos do projeto sempre que possível.
- Não instale dependências na máquina local; instale apenas dentro de container.
- Se o container não estiver disponível e a tarefa exigir dependência local, peça autorização antes.
- Dentro de containers do projeto, instalações e comandos necessários para a tarefa estão liberados.

## Código orientado a agentes

- Prefira arquivos pequenos e coesos; evite concentrar muitas responsabilidades no mesmo arquivo.
- Prefira funções curtas, com uma responsabilidade clara e nomes específicos.
- Evite aninhamento profundo; use retornos antecipados quando isso simplificar a leitura.
- Use nomes pesquisáveis e alinhados ao domínio. Evite nomes genéricos como `data`, `handler`, `manager` ou `service` sem contexto.
- Antes de criar abstração nova, procure duplicação real com `rg`.
- Não duplique regra de negócio; extraia para classe, função ou DataType apropriado.
- Comentários devem explicar decisões, restrições, exceções ou contexto não óbvio.
- Mensagens de erro devem ter contexto suficiente para debug sem expor dados sensíveis.

## DataTypes

- Sempre que possível, use DataTypes do domínio em assinaturas e regras de negócio em vez de tipos primitivos genéricos.
- Crie DataType quando o valor tiver validação própria, aparecer em mais de um fluxo ou representar conceito do domínio.
- Não crie DataType apenas para embrulhar primitivo sem regra.
- Controllers podem receber dados brutos da requisição, mas devem convertê-los para DataTypes antes de chamar classes de negócio.

## Dependências

- Injete dependências por construtor ou parâmetro quando a classe tiver regra de negócio testável.
- Não instancie integrações externas diretamente dentro do domínio.
- Encapsule fornecedores externos atrás de interfaces ou adapters do projeto.
- Use fakes nomeados em testes quando precisar substituir I/O externo.

## Git e pull requests

- PRs devem receber labels existentes em `.github/labels.yml`.
- Antes de criar ou atualizar labels de um PR, consulte `.github/labels.yml` e use somente labels listadas nesse arquivo, salvo orientação explícita diferente.
- As labels são usadas por workflows e automações; não deixe PR criado por agente sem label quando a ferramenta permitir aplicar labels.

## Verificações

- API: execute `cd api && composer check`.
- Testes da API: execute `cd api && composer test`.
- Para mudanças pequenas, rode primeiro o check do módulo alterado.
- Antes de commit autorizado, rode todos os checks aplicáveis.
- Execute comandos preferencialmente dentro do container quando o ambiente Docker estiver disponível.

## Documentação

- Mudanças de padrão do projeto devem ser registradas em `docs/conventions.md`.
- Se uma regra nova conflitar com código legado, mantenha compatibilidade e registre a exceção no documento de convenções.

## Escopo

- Ao tocar área legada, preserve o padrão local se a migração não fizer parte da tarefa.
- Não misture mudança funcional com migração arquitetural ampla.
- Se encontrar violação arquitetural fora do escopo, informe no final e não altere sem autorização.
