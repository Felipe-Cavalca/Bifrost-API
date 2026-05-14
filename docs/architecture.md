# Padrao de arquitetura

Este documento define o padrao de arquitetura do Bifrost-API para orientar a evolucao do framework e manter o core pequeno, previsivel e reutilizavel.

## Objetivo

O Bifrost-API e um micro-framework PHP para APIs HTTP. A arquitetura deve favorecer:

- separacao clara entre entrada HTTP, core, contratos, integracoes e utilitarios;
- baixo acoplamento com fornecedores externos;
- comportamento previsivel para roteamento, respostas, atributos, cache, fila, banco, storage e logs;
- evolucao incremental sem misturar melhorias de core com regras de produto;
- testes focados em contratos publicos, regressao e integracoes criticas.

## Visao geral

```text
.
|-- api/                  # Framework PHP, contratos, core, integracoes e testes
|-- docs/                 # Documentacao tecnica do projeto
|-- .github/workflows/    # Pipelines e automacoes
|-- .devcontainer/        # Ambiente de desenvolvimento
|-- .env.example          # Configuracao local de referencia
`-- README.md             # Documentacao de uso
```

Areas principais:

- `api/`: codigo PHP do framework.
- `docs/`: padroes tecnicos e decisoes de arquitetura.
- `.github/`: workflows, templates, labels e automacoes.
- `.devcontainer/` e `api/Docker/`: ambiente de desenvolvimento e imagens da API.

## Mapa rapido de alteracao

- Entrada HTTP: `api/index.php`, `api/Core/Request.php`, `api/Core/Get.php`, `api/Core/Post.php` e `api/Controller/`.
- Respostas HTTP: `api/Class/HttpResponse.php` e `api/Interface/Responseable.php`.
- Attributes: `api/Attributes/` e contratos em `api/Interface/Attribute*.php`.
- Configuracao: `api/Core/Settings.php`, `.env.example` e arquivos Docker.
- Observabilidade: `api/Core/Logger.php`, headers de request id e testes de core.
- Cache e fila: `api/Core/Cache.php`, `api/Core/Queue.php`, `api/Integration/Cache/` e `api/Integration/Queue/`.
- Banco de dados: `api/Core/Database.php`, `api/Integration/Database/` e `api/Interface/Database.php`.
- Storage: `api/Integration/`, `api/Interface/Storage.php` e adapters relacionados.
- DataTypes e validacoes: `api/DataTypes/`, `api/Enum/Field.php` e `api/Include/`.
- Testes: `api/tests/`.

## Camadas do framework

### Entrada HTTP

Responsavel por receber a requisicao, resolver controller/action, executar atributos e devolver uma resposta serializavel.

Diretorios e arquivos relacionados:

- `api/index.php`
- `api/Core/Request.php`
- `api/Core/Get.php`
- `api/Core/Post.php`
- `api/Controller/`
- `api/Attributes/`
- `api/Class/HttpResponse.php`

Regras:

- Controllers devem ser finos e demonstrar o contrato HTTP esperado.
- Controllers do framework nao devem concentrar regra de produto.
- Validacoes transversais devem ficar em attributes, DataTypes ou objetos dedicados.
- Respostas HTTP devem usar `HttpResponse` ou outro `Responseable`.
- O ciclo de request deve preservar compatibilidade com attributes `before` e `after`.

### Core

Responsavel pelos comportamentos centrais do framework.

Diretorios e arquivos relacionados:

- `api/Core/Request.php`
- `api/Core/Settings.php`
- `api/Core/Logger.php`
- `api/Core/Cache.php`
- `api/Core/Queue.php`
- `api/Core/Database.php`
- `api/Core/Autoload.php`
- `api/Core/AppError.php`

Regras:

- O core deve manter responsabilidades pequenas e pesquisaveis.
- O core pode orquestrar contratos e adapters, mas nao deve depender de regra de produto.
- Melhorias de observabilidade devem evitar exposicao de segredos, tokens, query strings sensiveis e caminhos internos.
- Mudancas em `Request`, `Settings`, `Logger`, `Cache`, `Queue` ou `Database` exigem testes focados e, quando possivel, a suite completa.

### Contratos

Responsaveis por estabilizar pontos de extensao do framework.

Diretorios relacionados:

- `api/Interface/`

Contratos atuais:

- `Attribute`
- `AttributeBefore`
- `AttributeAfter`
- `Cache`
- `Controller`
- `Database`
- `Insertable`
- `NoSqlDatabase`
- `PdoDriverAdapter`
- `Queue`
- `Responseable`
- `Storage`
- `Task`

Regras:

- Crie interface quando houver mais de uma implementacao provavel ou quando a dependencia externa for relevante.
- Evite interfaces para classes simples sem variacao prevista.
- Classes de core e integracao devem depender de contratos quando isso reduzir acoplamento real.

### Integracoes

Responsaveis por detalhes tecnicos de fornecedores externos.

Diretorios relacionados:

- `api/Integration/`
- `api/Docker/`

Regras:

- Redis, S3, MongoDB, banco e outros fornecedores devem ficar atras de contratos ou adapters.
- Codigo de core nao deve depender diretamente de SDKs quando houver contrato interno aplicavel.
- Adapters devem traduzir detalhes externos para estruturas e excecoes coerentes com o framework.
- Integracoes que dependem de ambiente externo devem ter testes que possam ser pulados claramente quando o ambiente nao estiver configurado, sem mascarar testes unitarios de configuracao.

### DataTypes

Responsaveis por validar valores reutilizaveis e dar significado a dados importantes.

Diretorios relacionados:

- `api/DataTypes/`
- `api/Enum/Field.php`
- `api/Include/AbstractFieldValue.php`

Regras:

- Use DataTypes em assinaturas quando o valor tiver validacao propria ou significado de dominio tecnico, como `UUID`, `Url`, `FilePath` e `Base64`.
- Nao crie DataType apenas para embrulhar primitivo sem regra.
- Novos DataTypes devem ter testes.
- DataTypes devem evitar I/O e efeitos colaterais.

### Observabilidade

Responsavel por correlacionar execucoes, registrar eventos e facilitar diagnostico.

Arquivos relacionados:

- `api/Core/Logger.php`
- `api/Core/Request.php`
- `api/Class/HttpResponse.php`
- `.env.example`

Regras:

- Request id deve existir quando logs estiverem ativos.
- Quando logs estiverem desativados, o framework deve evitar gerar e expor request id sem necessidade.
- Logs devem ter contexto suficiente para diagnostico sem expor segredos, tokens, dados privados, query strings completas ou caminhos internos sensiveis.
- O logger deve ser generico e poder ser chamado por qualquer parte do framework.
- Drivers de log externos, como MongoDB, devem ser opcionais e ter fallback para nao quebrar o fluxo de request quando a integracao estiver indisponivel.
- O `Request` decide quando associar informacao de request a uma resposta HTTP; `HttpResponse` define como essa informacao entra no payload.

### Testes

Estrategia:

- Testes unitarios para DataTypes, respostas, attributes e helpers.
- Testes de core para request, settings, logger, cache, queue e database.
- Testes de integracao para Redis, S3 e PDO quando houver ambiente configurado.
- Testes de regressao para bugs corrigidos.

Regras:

- Nova funcao publica deve ter teste correspondente.
- Mudanca de comportamento existente deve preservar compatibilidade quando possivel.
- Testes devem focar comportamento, nao detalhes internos frageis.
- Testes dependentes de servico externo devem declarar skip quando o ambiente nao estiver configurado.
- A verificacao padrao da API deve ser `composer check` dentro de `api/`.

## Variaveis de ambiente

Regras:

- Use prefixo `BFR_API_`.
- Documente novas variaveis no `.env.example`.
- Variaveis sensiveis nao devem aparecer com valores reais.
- Ao adicionar variavel usada pelo codigo, atualize tambem README quando ela fizer parte da configuracao publica.

## Seguranca operacional

Regras minimas:

- Nao exponha segredos, tokens ou credenciais em logs, respostas, commits ou PRs.
- Nao registre query string completa por padrao.
- Nao exponha detalhes internos de infraestrutura em mensagens de erro publicas sem decisao explicita.
- Falhas de integracao devem produzir erros claros para diagnostico sem vazar dados sensiveis.

## Como adicionar uma melhoria de core

Fluxo recomendado:

1. Criar branch propria no formato `module/function`.
2. Identificar a camada afetada.
3. Ler os contratos e testes existentes da area.
4. Fazer a menor mudanca coerente com o padrao local.
5. Criar testes focados.
6. Atualizar `.env.example`, README ou docs quando a mudanca afetar configuracao, uso ou arquitetura.
7. Rodar `composer check` dentro do container ou ambiente aprovado.

## Regras de decisao

- Prefira solucao simples enquanto o framework nao exigir abstracao maior.
- Introduza abstracoes apenas quando reduzirem acoplamento real ou duplicacao relevante.
- Nao misture melhoria funcional com migracao arquitetural ampla.
- Preserve compatibilidade publica sempre que possivel.
- Ao tocar area legada, siga o padrao local se a migracao nao fizer parte da tarefa.
- Se uma regra nova conflitar com codigo legado, registre a excecao em `docs/conventions.md`.
