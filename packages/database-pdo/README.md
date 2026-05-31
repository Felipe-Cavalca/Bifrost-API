# bifrost/database-pdo

Fabrica opcional de conexoes PDO e helper simples de consultas para o Bifrost Framework.

Registre `PdoExtension` com `dsn`, `username`, `password` e `options`, ou use
`connections` para configurar multiplas conexoes nomeadas.

Ao registrar a extensao, o container tambem recebe `PdoDatabase`, com metodos
para `execute`, `fetch`, `fetchAll`, `value`, `select`, `insert`, `update`,
`delete` e `exists`.
