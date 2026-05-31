# Mapa do Framework

## Core

- `Application`: objeto principal da aplicacao.
- `Container`: registro e resolucao de dependencias.
- `HttpKernel`: lifecycle HTTP.
- `Request`: metodo, path, query, body, headers e request-id.
- `Response`: JSON/texto/status/headers e helpers HTTP comuns.
- `Router` e `Route`: roteamento HTTP.
- `ControllerResolver`: invoca controllers e valida attributes.
- `HttpException`: excecao HTTP padronizada para respostas JSON com status, erros e headers.

## Observabilidade HTTP

- `Request` le `X-Request-Id` ou gera um identificador quando o header nao existe.
- `HttpKernel` inclui `X-Request-Id` em toda resposta.
- Respostas JSON de erro recebem `request_id` no payload.

## Attributes

- `Method`
- `RequiredFields`
- `OptionalFields`
- `RequiredParams`
- `OptionalParams`
- `Details`
- `Response`

## Contracts

- `Extension`
- `CacheStore`
- `Queue`
- `DatabaseConnectionFactory`
- `Storage`
- `DataType`
- `HttpAttribute`
- `RequestValidatorAttribute`
