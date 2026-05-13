# Convenções de nomes

Este documento define os padrões de nomes do Dossier para branches, commits, PRs, issues, arquivos, código, banco de dados e API.

## Princípios

- Use nomes em inglês para código, pastas técnicas, branches, endpoints e banco de dados.
- Use português do Brasil em documentação, commits, PRs, issues e comentários de planejamento.
- Use nomes curtos, descritivos e sem abreviações pouco claras.
- Não use acentos, espaços ou caracteres especiais em nomes técnicos.
- Cada tarefa diferente deve ter uma branch diferente.
- Prefira manter o padrão já existente do módulo alterado.
- Prefira nomes pesquisáveis e específicos ao domínio.
- Evite nomes genéricos como `data`, `handler`, `manager` ou `helper` quando não houver contexto claro.
- Comentários devem explicar decisões, restrições, exceções, regras de negócio ou integrações não óbvias.
- Não use comentários para repetir o que o código já expressa.

## Código orientado a agentes

Regras para manter o código fácil de navegar, alterar e validar por agentes e humanos:

- Mantenha arquivos coesos e com poucas responsabilidades.
- Prefira funções pequenas, com uma intenção clara.
- Evite aninhamento profundo; use validações iniciais e retornos antecipados quando isso simplificar o fluxo.
- Antes de criar uma abstração, procure duplicação real no código com `rg`.
- Não duplique regra de negócio entre controller, class de negócio, model ou frontend.
- Mensagens de erro devem ter contexto suficiente para debug sem expor tokens, caminhos internos sensíveis ou dados privados.
- Ao alterar comentário próximo a código modificado, mantenha o comentário coerente com o comportamento atual.

## Branches

Branches devem seguir o formato por módulo e função:

```text
module/function
```

Onde:

- `module` identifica a área principal alterada.
- `function` descreve a tarefa ou comportamento alterado.
- Ambos devem usar letras minúsculas e `kebab-case` quando houver mais de uma palavra.

Exemplos:

```text
docs/conventions
ci/php-tests
```

Módulos recomendados:

| Módulo | Uso |
| --- | --- |
| `docs` | documentação |
| `ci` | pipelines, checks e automações |
| `infra` | Docker, deploy, observabilidade e ambiente |
| `chore` | manutenções sem módulo claro |

Regras:

- Crie a branch a partir de `main`, salvo quando a tarefa depender de outra branch.
- Não misture tarefas independentes na mesma branch.
- Não inclua número de issue no nome da branch por padrão.
- Se a branch resolver uma subissue, mantenha o nome focado no módulo e na função.

## Commits

Use Conventional Commits em português do Brasil:

```text
tipo: descrição curta
```

Tipos permitidos:

- `feat`: nova funcionalidade
- `fix`: correção de bug
- `docs`: documentação
- `test`: testes
- `refactor`: refatoração sem mudança de comportamento
- `chore`: manutenção ou configuração interna
- `ci`: pipelines e automações
- `build`: build, imagens e empacotamento
- `perf`: melhoria de performance

Exemplos:

```text
feat: adiciona upload de documentos
fix: corrige validação de tamanho do arquivo
docs: define convenções de nomes
test: adiciona testes de login
ci: adiciona testes php no pull request
```

## Pull requests

Antes de abrir ou editar um PR, consulte `.github/PULL_REQUEST_TEMPLATE.md`.

Título do PR:

```text
tipo: descrição curta
```

Descrição do PR:

- Objetivo
- Principais mudanças
- Testes executados
- Pontos de atenção
- Issues relacionadas

Sempre aplique labels adequadas ao PR.

## Issues

Título recomendado:

```text
[M{numero}] tipo: descrição curta
```

Exemplos:

```text
[M1] docs: definir convenções de nomes
[M2] feat: criar endpoint de login
[M3] feat: implementar upload de documentos
```

Quando a issue for subissue, mantenha o título focado na tarefa concreta.

## Pastas e arquivos

Padrão geral:

- Backend PHP: seguir o padrão PSR-4 já existente, com pastas e classes em `PascalCase`.
- Configurações: usar nomes esperados pelas ferramentas, como `docker-compose.yml`, `phpunit.xml` e `manifest.json`.
- Documentação: usar `kebab-case`, exceto arquivos convencionais como `README.md` e `AGENTS.md`.

Exemplos:

```text
api/Core/Request.php
api/Integration/Database/PdoDatabase.php
api/tests/Core/RequestTest.php
app/pages/index.html
app/js/pages/document-list.js
docs/conventions.md
```

## PHP

Namespaces:

- Devem acompanhar a estrutura de pastas do backend.
- Use `PascalCase` nos segmentos do namespace.

Classes, interfaces, traits e enums:

- Use `PascalCase`.
- O nome do arquivo deve corresponder ao nome da classe quando houver uma classe principal.

Exemplos:

```text
DocumentController
LocalStorageAdapter
DocumentUpload
DocumentStatus
```

Camadas de negócio e persistência:

- Controllers, rotas e handlers não devem acessar models diretamente.
- Use uma classe em `api/Class` para concentrar regras de negócio, validações e orquestração de persistência.
- A classe de negócio pode chamar o model correspondente quando precisar inserir, consultar ou alterar dados.
- Models devem ficar focados em persistência e consultas.
- Exceções são permitidas apenas quando uma classe de negócio não fizer sentido para o caso, por exemplo leitura técnica simples sem regra de negócio.
- Sempre que possível, valores de domínio devem ser tipados com DataTypes em assinaturas de métodos, construtores e regras de negócio, em vez de `string`, `int` ou `array` genéricos.
- Campos com validação reutilizável devem usar DataTypes. Crie um DataType quando a regra for usada em mais de um lugar ou representar um conceito próprio do domínio, como `UUID`, `Email`, `Password` ou `UserRole`.
- Não crie DataType apenas para encapsular um primitivo sem regra, validação ou significado próprio.
- Controllers podem receber dados brutos da requisição, mas devem convertê-los para DataTypes antes de chamar classes de negócio.
- Novas chamadas de funções, métodos e construtores devem usar `named arguments` para evitar que mudanças na ordem dos parâmetros quebrem chamadas no futuro. Código legado pode permanecer com argumentos posicionais até ser alterado por outro motivo.

Métodos e variáveis:

- Use `camelCase`.
- Use verbos para métodos que executam ações.

Exemplos:

```text
uploadDocument()
validateFileSize()
createPresignedUrl()
$documentId
$storageAdapter
```

Constantes:

- Use `UPPER_SNAKE_CASE`.

Exemplos:

```text
MAX_FILE_SIZE
DEFAULT_STORAGE_PATH
```

Testes:

- Classes de teste devem terminar com `Test`.
- Arquivos de teste devem terminar com `Test.php`.

Exemplos:

```text
DocumentUploadTest.php
StorageAdapterTest.php
```

## API

Endpoints:

- Use recursos no plural.
- Use `kebab-case` quando houver mais de uma palavra.
- Use query string para identificar ou filtrar recursos.
- Use ações no caminho apenas quando a operação não encaixar bem em CRUD.
- Não use parâmetros de rota no formato `/resource/{id}` no padrão atual do projeto.

Exemplos:

```text
GET /documents
POST /documents
GET /documents?id={uuid}
PATCH /documents?id={uuid}
DELETE /documents?id={uuid}
POST /documents/restore?id={uuid}
GET /document-types
POST /documents/reprocess?id={uuid}
```

Parâmetros e payloads:

- Use `snake_case` em JSON e parâmetros quando representarem dados persistidos.

Exemplos:

```json
{
  "document_type_id": "uuid",
  "original_name": "contrato.pdf",
  "expires_at": "2026-12-31"
}
```

## Variáveis de ambiente

Use prefixo do projeto e `UPPER_SNAKE_CASE`:

Variáveis legadas podem permanecer até serem migradas.
