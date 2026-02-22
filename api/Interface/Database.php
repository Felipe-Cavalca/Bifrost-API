<?php

namespace Bifrost\Interface;

use PDO;

interface Database
{
    public function getConnection(): PDO;

    public function getDriver(): string;

    public function hasReturning(): bool;

    public function begin(): bool;

    public function rollback(): bool;

    public function save(): bool;

    public function executeQuery(string $sql, array $params = []): mixed;

    public function insert(string $table, array $data, string $returning = ""): int|false|string;

    public function update(string $table, array $data, array|string $where): bool;

    public function delete(string $table, array|string $where): bool;

    public function select(
        string $table,
        array|string $fields = "*",
        ?array $join = null,
        null|array|string $where = null,
        ?string $group = null,
        null|array|string $having = null,
        ?string $order = null,
        ?string $limit = null
    ): array;

    public function getTables(): array;

    public function getDetTable(string $table): array;

    public function existTable(string $table): bool;

    public function existField(string $table, string $field): bool;

    public function setSystemIdentifier(array $data): bool;

    public function exists(string $table, array|string $where): bool;

    public function query(
        null|array|string $select = null,
        ?array $insert = null,
        ?string $update = null,
        ?string $delete = null,
        ?string $into = null,
        ?string $from = null,
        ?array $set = null,
        null|array|string $where = null,
        null|array|string $join = null,
        ?string $order = null,
        ?string $limit = null,
        null|array|string $having = null,
        ?string $group = null,
        ?string $returning = null,
        ?string $query = null,
        ?array $params = [],
        ?bool $returnFirst = false,
        ?bool $exists = false
    ): array|bool;
}
