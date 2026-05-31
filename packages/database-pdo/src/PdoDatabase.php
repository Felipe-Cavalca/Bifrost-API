<?php

declare(strict_types=1);

namespace Bifrost\Extension\DatabasePdo;

use Bifrost\Framework\Contracts\TransactionManager;
use InvalidArgumentException;
use PDO;
use PDOStatement;

final class PdoDatabase implements TransactionManager
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function connection(): PDO
    {
        return $this->connection;
    }

    public function driver(): string
    {
        $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);

        return is_string($driver) ? $driver : 'unknown';
    }

    public function supportsReturning(): bool
    {
        return $this->driver() === 'pgsql';
    }

    public function begin(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function execute(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function value(string $sql, array $params = []): mixed
    {
        $value = $this->execute($sql, $params)->fetchColumn();

        return $value === false ? null : $value;
    }

    public function select(
        string $table,
        array|string $columns = '*',
        array|string|null $where = null,
        ?string $orderBy = null,
        int|string|null $limit = null
    ): array {
        $params = [];
        $sql = 'SELECT ' . $this->columnsSql($columns) . " FROM $table";
        $whereSql = $this->whereSql($where, $params);

        if ($whereSql !== '') {
            $sql .= " WHERE $whereSql";
        }

        if ($orderBy !== null && $orderBy !== '') {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit !== null && $limit !== '') {
            $sql .= " LIMIT $limit";
        }

        return $this->fetchAll($sql, $params);
    }

    public function insert(string $table, array $data, ?string $returning = null): int|string
    {
        if ($data === []) {
            throw new InvalidArgumentException('Insert PDO deve receber ao menos um campo.');
        }

        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

        if ($returning !== null && $returning !== '' && $this->supportsReturning()) {
            $sql .= " RETURNING $returning";
            $value = $this->value($sql, $data);

            return is_int($value) || is_string($value) ? $value : (string) $value;
        }

        $this->execute($sql, $data);

        return $this->connection->lastInsertId();
    }

    public function update(string $table, array $data, array|string $where): int
    {
        if ($data === []) {
            throw new InvalidArgumentException('Update PDO deve receber ao menos um campo.');
        }

        $params = [];
        $sets = [];

        foreach ($data as $column => $value) {
            if (!is_string($column) || $column === '') {
                throw new InvalidArgumentException('Update PDO deve receber campos nomeados.');
            }

            $placeholder = 'set_' . $column;
            $sets[] = "$column = :$placeholder";
            $params[$placeholder] = $value;
        }

        $whereSql = $this->whereSql($where, $params);
        $statement = $this->execute("UPDATE $table SET " . implode(', ', $sets) . " WHERE $whereSql", $params);

        return $statement->rowCount();
    }

    public function delete(string $table, array|string $where): int
    {
        $params = [];
        $whereSql = $this->whereSql($where, $params);
        $statement = $this->execute("DELETE FROM $table WHERE $whereSql", $params);

        return $statement->rowCount();
    }

    public function exists(string $table, array|string|null $where = null): bool
    {
        $params = [];
        $whereSql = $this->whereSql($where, $params);
        $sql = "SELECT 1 FROM $table" . ($whereSql === '' ? '' : " WHERE $whereSql") . ' LIMIT 1';

        return $this->value($sql, $params) !== null;
    }

    private function columnsSql(array|string $columns): string
    {
        if (is_string($columns)) {
            return $columns;
        }

        if ($columns === []) {
            return '*';
        }

        return implode(', ', $columns);
    }

    private function whereSql(array|string|null $where, array &$params): string
    {
        if ($where === null || $where === []) {
            return '';
        }

        if (is_string($where)) {
            return $where;
        }

        $parts = [];
        foreach ($where as $column => $value) {
            if (is_int($column)) {
                $parts[] = (string) $value;
                continue;
            }

            if ($value === null) {
                $parts[] = "$column IS NULL";
                continue;
            }

            if (is_array($value)) {
                $placeholders = [];
                foreach (array_values($value) as $index => $item) {
                    $placeholder = 'where_' . $column . '_' . $index;
                    $placeholders[] = ":$placeholder";
                    $params[$placeholder] = $item;
                }

                $parts[] = "$column IN (" . implode(', ', $placeholders) . ')';
                continue;
            }

            $placeholder = 'where_' . $column;
            $parts[] = "$column = :$placeholder";
            $params[$placeholder] = $value;
        }

        return implode(' AND ', $parts);
    }
}
