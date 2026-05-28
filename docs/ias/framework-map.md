# Mapa do Framework

## Core

- `Application`: objeto principal da aplicacao.
- `Container`: registro e resolucao de dependencias.
- `HttpKernel`: lifecycle HTTP.
- `Request`: metodo, path, query, body e headers.
- `Response`: JSON/texto/status/headers.
- `Router` e `Route`: roteamento HTTP.
- `ControllerResolver`: invoca controllers e valida attributes.

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
