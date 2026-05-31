<?php

declare(strict_types=1);

namespace Bifrost\Extension\DatabasePdo;

use Bifrost\Framework\Contracts\TransactionManager;
use InvalidArgumentException;
use PDO;
use PDOStatement;

final class PdoDatabase implements TransactionManager
{
    /**
     * @param PDO $connection Conexao PDO reutilizada pelo banco.
     */
    public function __construct(private readonly PDO $connection)
    {
    }

    /**
     * Retorna a conexao PDO interna.
     */
    public function connection(): PDO
    {
        return $this->connection;
    }

    /**
     * Retorna o driver PDO ativo.
     */
    public function driver(): string
    {
        $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);

        return is_string($driver) ? $driver : 'unknown';
    }

    /**
     * Indica se o driver suporta RETURNING em inserts.
     */
    public function supportsReturning(): bool
    {
        return $this->driver() === 'pgsql';
    }

    /**
     * Inicia uma transacao.
     */
    public function begin(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Desfaz a transacao atual.
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Confirma a transacao atual.
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Executa SQL preparado com parametros.
     *
     * @param array<string, mixed> $params
     */
    public function execute(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /**
     * Retorna todas as linhas como arrays associativos.
     *
     * @param array<string, mixed> $params
     * @return list<array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna a primeira linha ou null.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * Retorna a primeira coluna da primeira linha.
     *
     * @param array<string, mixed> $params
     */
    public function value(string $sql, array $params = []): mixed
    {
        $value = $this->execute($sql, $params)->fetchColumn();

        return $value === false ? null : $value;
    }

    /**
     * Monta e executa uma consulta SELECT simples.
     *
     * @param array<int, string>|string $columns
     * @param array<string, mixed>|string|null $where
     * @return list<array<string, mixed>>
     */
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

    /**
     * Insere uma linha e retorna o id gerado ou o valor de RETURNING.
     *
     * @param array<string, mixed> $data
     */
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

    /**
     * Atualiza linhas que batem com a condicao.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed>|string $where
     */
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

    /**
     * Remove linhas que batem com a condicao.
     *
     * @param array<string, mixed>|string $where
     */
    public function delete(string $table, array|string $where): int
    {
        $params = [];
        $whereSql = $this->whereSql($where, $params);
        $statement = $this->execute("DELETE FROM $table WHERE $whereSql", $params);

        return $statement->rowCount();
    }

    /**
     * Verifica se existe ao menos uma linha para a condicao.
     *
     * @param array<string, mixed>|string|null $where
     */
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
