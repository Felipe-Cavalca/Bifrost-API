# Padrão de arquitetura

Este documento define o padrão de arquitetura do Dossier para orientar as próximas entregas da milestone M1 e manter a evolução do projeto consistente.

## Objetivo

O Dossier será um SaaS de gestão documental. A arquitetura deve favorecer:

- separação clara entre interface, API, domínio, infraestrutura e tarefas assíncronas;
- evolução incremental por milestones;
- baixo acoplamento com fornecedores externos;
- testes focados em regras de negócio e integrações críticas;
- suporte futuro a OCR, busca, permissões, auditoria, backup e storage externo.

## Visão geral

```text
.
|-- api/                  # Backend HTTP, domínio, integrações e workers
|-- app/                  # Interface web estática
|-- database/             # Banco PostgreSQL, instalação e updates SQL
|-- docs/                 # Documentação técnica do projeto
|-- scripts/              # Scripts auxiliares do repositório
|-- .github/workflows/    # Pipelines e automações
|-- .devcontainer/        # Ambiente de desenvolvimento
|-- docker-compose.yml    # Orquestração local principal
`-- .env.example          # Configuração local de referência
```

O projeto deve manter três áreas principais:

- `api/`: backend responsável por regras, persistência, processamento e exposição HTTP.
- `app/`: frontend responsável por interação do usuário, telas e consumo da API.
- `database/`: estrutura do PostgreSQL, instalação e atualizações de schema.

Também existem áreas de apoio:

- `scripts/`: automações auxiliares do repositório.
- `.devcontainer/`: ambiente de desenvolvimento em container.
- `.github/`: workflows, template de PR e automações do GitHub.

## Mapa rápido de alteração

- Endpoint HTTP: `api/Controller/`, `api/index.php` e testes HTTP.
- Regra de negócio: `api/Class/`, `api/DataTypes/` e `api/Enum/`.
- Persistência: `api/Model/` e scripts SQL em `database/`.
- Integração externa: `api/Integration/` e contratos em `api/Interface/`.
- Tarefas assíncronas: `api/Tasks/`, `api/Worker.php` e fila.
- Frontend: `app/pages/`, `app/js/`, `app/css/` e `app/components/`.
- Scripts auxiliares: `scripts/`, `api/scripts/` e `app/scripts/`.
- Configuração local: `.env.example`, `docker-compose.yml`, `.devcontainer/` e documentação relacionada.

## Camadas do backend

O backend deve seguir uma separação por responsabilidades.

### Entrada HTTP

Responsável por receber requisições, validar contrato básico e devolver respostas HTTP.

Diretórios atuais relacionados:

- `api/index.php`
- `api/Controller/`
- `api/Attributes/`
- `api/Class/HttpResponse.php`
- `api/Core/Request.php`

Regras:

- Controllers devem ser finos.
- Controllers não devem concentrar regra de negócio complexa.
- Validações comuns devem ficar em atributos, serviços ou objetos dedicados.
- Respostas devem usar o formato padronizado por `HttpResponse`.

### Domínio

Responsável pelas regras centrais do Dossier.

Diretórios atuais relacionados:

- `api/Class/`
- `api/DataTypes/`
- `api/Enum/`

Regras:

- Regras de documento, usuário, organização, permissões, tags, tipos e metadados devem ficar fora dos controllers.
- Classes em `api/Class/` devem representar casos de uso ou operações de negócio claras, como autenticação, cadastro, upload, listagem, restauração e reprocessamento.
- DataTypes devem encapsular validações recorrentes, como UUID, email, senha, caminho, URL e papéis de usuário.
- Enums devem representar conjuntos fechados de estados, tipos ou opções do domínio.
- Não introduza diretórios como `api/Service/` ou `api/ValueObject/` sem uma tarefa específica de arquitetura.

### Persistência

Responsável por acesso ao banco e montagem de queries.

Diretórios atuais relacionados:

- `api/Core/Database.php`
- `api/Integration/Database/`
- `api/Interface/Database.php`
- `api/Model/`
- `database/src/`
- `database/install/`
- `database/update/`

Regras:

- Acesso a tabelas e consultas deve ficar em `api/Model/` ou nas integrações de banco.
- Classes de negócio em `api/Class/` podem chamar models quando precisarem inserir, consultar ou alterar dados.
- Models devem ficar focados em persistência e consultas.
- Scripts SQL em `database/` devem ser a fonte de verdade da estrutura do banco.
- Não introduza `api/Repository/` sem uma tarefa específica de arquitetura.

### Infraestrutura

Responsável por integrações externas e detalhes técnicos.

Diretórios atuais relacionados:

- `api/Integration/`
- `api/Core/Cache.php`
- `api/Core/Queue.php`
- `api/Docker/`

Regras:

- Storage, Redis, S3, banco, OCR e outros fornecedores devem ser acessados por interfaces ou adapters.
- Código de domínio não deve depender diretamente de SDKs externos.
- Adapters devem traduzir detalhes externos para contratos internos.

### Tarefas assíncronas

Responsável por processamento em segundo plano.

Diretórios atuais relacionados:

- `api/Tasks/`
- `api/Worker.php`
- `api/Core/Queue.php`
- `api/Integration/Queue/`

Regras:

- OCR, indexação, geração de preview e reprocessamento devem rodar por fila quando forem operações demoradas.
- Tarefas devem ser idempotentes sempre que possível.
- Falhas devem registrar logs e permitir retry.
- O worker não deve conter regra de negócio; ele deve executar tarefas.

## Camadas do frontend

O frontend atual é estático e deve permanecer simples enquanto o produto estiver nas primeiras milestones.

Diretórios atuais:

- `app/pages/`
- `app/components/`
- `app/js/`
- `app/css/`
- `app/assets/`
- `app/config/`
- `app/core/`

Regras:

- Páginas devem coordenar a tela e chamar funções de domínio do frontend.
- Componentes devem ser reutilizáveis e pequenos.
- Código de chamada HTTP deve ser centralizado para evitar duplicação.
- CSS de página deve ficar separado de CSS global.
- Assets devem ficar organizados por finalidade.

## Fluxo principal de documento

```text
Usuário
  -> App
  -> API Controller
  -> Class de negócio
  -> Model
  -> Database
  -> Storage
  -> Queue
  -> Worker
  -> OCR/Indexação
```

Fluxo esperado:

1. O usuário envia um arquivo pela interface ou API.
2. O controller recebe a requisição e valida o contrato básico.
3. Uma classe em `api/Class/` executa o caso de uso.
4. O arquivo é salvo pelo adapter de storage.
5. Os metadados são persistidos via model.
6. Uma tarefa assíncrona é enviada para fila quando houver processamento posterior.
7. O worker processa OCR, preview ou indexação.
8. A API passa a devolver o documento com status atualizado.

## Módulos funcionais

Os módulos principais previstos são:

- `auth`: login, sessão, JWT e proteção de rotas.
- `user`: usuários, cadastro, senha e admin inicial.
- `organization`: organizações e isolamento de dados.
- `permission`: papéis, permissões e middleware.
- `document`: entidade principal, metadados e status.
- `file`: upload, validação, storage físico e download.
- `folder`: organização em pastas.
- `tag`: tags e relacionamento com documentos.
- `document-type`: tipos de documento.
- `metadata`: campos customizados e valores.
- `storage`: storage local e S3-compatible.
- `queue`: fila, worker e retry.
- `ocr`: OCR e extração de texto.
- `search`: indexação, busca textual e filtros.
- `audit`: auditoria e timeline.
- `dashboard`: indicadores e visão geral.

## Contratos internos

Integrações devem ser acessadas por contratos internos quando houver risco de troca de implementação.

Contratos atuais:

- `api/Interface/Database.php`
- `api/Interface/Storage.php`
- `api/Interface/Queue.php`
- `api/Interface/Cache.php`
- `api/Interface/Task.php`
- `api/Interface/Controller.php`
- `api/Interface/Responseable.php`

Regras:

- Crie interface quando houver mais de uma implementação provável ou quando a dependência externa for relevante.
- Evite criar interfaces para classes simples sem variação prevista.
- Classes de negócio devem depender de contratos quando isso reduzir acoplamento real.

## Banco de dados

O banco deve refletir os módulos do produto e fica organizado em `database/`.

Diretórios atuais:

- `database/src/`: scripts base e objetos SQL compartilhados.
- `database/install/`: instalação inicial do schema.
- `database/update/`: atualizações incrementais do banco.
- `database/docker-compose.yml`: ambiente isolado do banco.
- `database/Dockerfile`, `database/entrypoint.sh` e `database/update.sh`: automação do container e aplicação dos scripts.

Entidades previstas:

- usuários;
- organizações;
- documentos;
- arquivos de documentos;
- tags;
- tipos de documento;
- metadados;
- permissões;
- auditoria;
- tarefas de processamento, se necessário.

Regras:

- Toda alteração estrutural deve ter script SQL em `database/`.
- Instalação inicial e atualizações devem permanecer coerentes.
- Soft delete deve ser usado para documentos e entidades sensíveis.
- Dados devem ser isolados por organização quando multiusuário estiver ativo.
- Auditoria deve registrar ações relevantes em documentos.

## Storage

O storage deve suportar implementação local no início e S3-compatible posteriormente.

Regras:

- A API não deve espalhar caminhos físicos de arquivos.
- O domínio deve tratar arquivos por identificadores e metadados.
- Download deve passar por validação de permissão.
- Adapters de storage devem implementar contrato comum.

## Busca e OCR

Busca e OCR devem ser tratados como pipeline de processamento.

Regras:

- Upload não deve depender de OCR síncrono.
- O documento deve ter status de processamento.
- Texto extraído deve ser persistido de forma pesquisável.
- Reprocessamento deve ser uma ação explícita.
- Falhas de OCR devem ser observáveis.

## Segurança

Regras mínimas:

- Validar tipo e tamanho de upload.
- Sanitizar nomes e caminhos de arquivo.
- Não confiar em MIME type informado pelo cliente sem validação.
- Proteger downloads por autenticação e permissão.
- Isolar dados por organização.
- Registrar auditoria de criação, edição, exclusão e download.
- Evitar exposição de caminhos internos e detalhes de infraestrutura.

## Observabilidade

Regras mínimas:

- Toda requisição deve ter request id.
- Erros devem ser registrados com contexto suficiente.
- Healthcheck deve validar disponibilidade básica da API.
- Falhas de fila, OCR e storage devem gerar logs claros.
- Logs não devem expor segredos, tokens ou dados sensíveis.

## Testes

Estratégia:

- Testes unitários para classes de negócio, validações e DataTypes.
- Testes de integração para banco, storage, cache e fila.
- Testes HTTP para controllers e contratos de resposta.
- Testes de regressão para bugs corrigidos.

Regras:

- Nova regra de negócio deve ter teste correspondente.
- Mudança em comportamento existente deve preservar compatibilidade quando possível.
- Testes devem focar comportamento, não detalhes internos frágeis.

## Como adicionar uma nova funcionalidade

Fluxo recomendado:

1. Criar branch própria para a tarefa.
2. Identificar o módulo funcional afetado.
3. Definir ou ajustar contrato HTTP.
4. Criar ou ajustar classe em `api/Class/` para o caso de uso.
5. Criar ou ajustar model quando houver persistência.
6. Criar ou ajustar adapter quando houver integração externa.
7. Criar testes focados.
8. Atualizar documentação quando a decisão afetar arquitetura, uso ou padrão.

## Regras de decisão

- Prefira solução simples enquanto a milestone ainda não exigir abstração maior.
- Introduza abstrações apenas quando reduzirem acoplamento real.
- Não coloque regra de negócio em controller.
- Não acesse SDK externo diretamente a partir do domínio.
- Não misture frontend, backend e infraestrutura na mesma responsabilidade.
- Não faça mudanças arquiteturais amplas junto com tarefas pequenas de produto.

## Exceções

O projeto ainda possui estruturas herdadas da base inicial. Código legado pode permanecer até que exista uma tarefa específica de migração.

Ao alterar uma área legada:

- preserve compatibilidade;
- siga o padrão local existente quando a migração não fizer parte do escopo;
- registre uma decisão se a mudança criar ou alterar regra arquitetural.
