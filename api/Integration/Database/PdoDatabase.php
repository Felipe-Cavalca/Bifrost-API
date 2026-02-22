<?php

namespace Bifrost\Integration\Database;

use Bifrost\Class\HttpResponse;
use Bifrost\Core\AppError;
use Bifrost\Core\Functions;
use Bifrost\Core\Settings;
use Bifrost\Integration\Database\Driver\MysqlPdoDriver;
use Bifrost\Integration\Database\Driver\PgsqlPdoDriver;
use Bifrost\Integration\Database\Driver\PdoDriverAdapter;
use Bifrost\Integration\Database\Driver\SqlitePdoDriver;
use Bifrost\Interface\Database as DatabaseInterface;
use Bifrost\Interface\Insertable;
use PDO;
use PDOException;

class PdoDatabase implements DatabaseInterface
{
    private array $drivers = [
        "sqlite" => "sqlite",
        "mysql" => "mysql",
        "pgsql" => "pgsql"
    ];
    private static array $connections = [];
    private static ?Settings $settings = null;
    private array $dataConn;
    private PdoDriverAdapter $driverAdapter;
    private PDO $conn;

    public function __construct(?string $databaseName = null)
    {
        self::$settings = new Settings();
        $this->dataConn = self::$settings->getSettingsDatabase($databaseName);
        $this->driverAdapter = $this->makeDriverAdapter($this->dataConn["driver"] ?? "pgsql");

        $connectionKey = ($databaseName ?? "__default__") . ":" . ($this->dataConn["driver"] ?? "pgsql");
        $this->conn = self::$connections[$connectionKey] ?? $this->conn();
        self::$connections[$connectionKey] = $this->conn;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }

    public function getDriver(): string
    {
        return $this->drivers[$this->conn->getAttribute(PDO::ATTR_DRIVER_NAME)] ?? "unknown";
    }

    public function hasReturning(): bool
    {
        return in_array($this->getDriver(), ["pgsql"], true);
    }

    private function conn(): PDO
    {
        return $this->driverAdapter->connect($this->dataConn);
    }

    private static function buildSelectQuery(string $table, array|string $fields = "*"): string
    {
        if (is_array($fields)) {
            $formattedFields = [];
            foreach ($fields as $alias => $field) {
                if (is_int($alias)) {
                    $formattedFields[] = $field;
                } else {
                    $formattedFields[] = "$alias AS $field";
                }
            }
            $fields = implode(", ", $formattedFields);
        }

        return "SELECT $fields FROM $table";
    }

    private static function buildInsertQuery(string $table, array $data, string $returning = ""): string
    {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            if ($value instanceof Insertable) {
                $value = $value->value();
            }

            if (is_int($key)) {
                $fields[] = $value;
                $values[] = ":{$value}";
            } else {
                $fields[] = $key;

                if (is_string($value)) {
                    $value = Functions::sanitize($value);
                    $values[] = "'{$value}'";
                } elseif (is_null($value)) {
                    $values[] = "NULL";
                } else {
                    $values[] = "{$value}";
                }
            }
        }

        $fieldsStr = implode(", ", $fields);
        $valuesStr = implode(", ", $values);
        $returningStr = empty($returning) ? "" : " RETURNING {$returning}";

        return "INSERT INTO {$table} ({$fieldsStr}) VALUES ({$valuesStr}){$returningStr}";
    }

    private static function buildUpdateQuery(string $table, array $data): string
    {
        $fields = [];
        $data = Functions::sanitizeArray($data);
        foreach ($data as $key => $value) {
            if ($value instanceof Insertable) {
                $value = $value->value();
            }
            
            if (is_string($value)) {
                $value = "'{$value}'";
            }
            $fields[] = "{$key} = {$value}";
        }

        $fieldsStr = implode(", ", $fields);

        return "UPDATE {$table} SET {$fieldsStr}";
    }

    private static function buildDeleteQuery(string $table): string
    {
        return "DELETE FROM $table";
    }

    private static function buildWhereQuery(array|string $where, bool $and = true): string
    {
        if (is_string($where)) {
            return $where;
        }

        $whereStr = [];

        foreach ($where as $key => $value) {
            if ($value instanceof Insertable) {
                $value = $value->value();
            }

            if (is_int($key)) {
                $whereStr[] = $value;
            } elseif (strtoupper($key) === "OR") {
                $whereStr[] = "(" . self::buildWhereQuery($value, false) . ")";
            } elseif (strtoupper($key) === "AND") {
                $whereStr[] = "(" . self::buildWhereQuery($value, true) . ")";
            } else {
                if ($value === null) {
                    $whereStr[] = "$key IS NULL";
                } elseif (is_array($value)) {
                    $whereStr[] = "$key IN ('" . implode("', '", Functions::sanitizeArray($value)) . "')";
                } elseif (is_int($value)) {
                    $whereStr[] = "$key = $value";
                } elseif (is_string($value)) {
                    $whereStr[] = "$key = '" . Functions::sanitize($value) . "'";
                }
            }
        }

        return implode(($and ? " AND " : " OR "), $whereStr);
    }

    private static function buildJoinQuery(array $join): string
    {
        return implode(" ", $join);
    }

    public function begin(): bool
    {
        return $this->conn->beginTransaction();
    }

    public function rollback(): bool
    {
        return $this->conn->rollBack();
    }

    public function save(): bool
    {
        return $this->conn->commit();
    }

    public function executeQuery(string $sql, array $params = []): mixed
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            // Verifica o tipo de query e retorna o resultado apropriado
            if (stripos($sql, 'SELECT') === 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } elseif (stripos($sql, 'INSERT') === 0 || stripos($sql, 'UPDATE') === 0 || stripos($sql, 'DELETE') === 0) {
                // Verifica se a consulta contém a cláusula RETURNING
                if (stripos($sql, 'RETURNING') !== false) {
                    return $stmt->fetchColumn();
                }
                return $stmt->rowCount();
            } else {
                return $stmt->rowCount();
            }
        } catch (PDOException $e) {
            // Lançar um erro de servidor interno com detalhes adicionais
            throw new AppError(HttpResponse::internalServerError(
                errors: ["Database error" => $e->getMessage()],
                message: "An error occurred while executing the database query."
            ));
        }
    }

    public function insert(string $table, array $data, string $returning = ""): int|false|string
    {
        $returning = $this->hasReturning() ? $returning : "";

        $sql = $this->buildInsertQuery($table, $data, $returning);

        $result = $this->executeQuery($sql);

        if ($returning) {
            return $result;
        }

        return $result !== false ? $this->conn->lastInsertId() : false;
    }

    public function update(string $table, array $data, array|string $where): bool
    {
        $sql = $this->buildUpdateQuery($table, $data);

        if (!empty($where)) {
            $sql .= " WHERE " . $this->buildWhereQuery($where);
        }

        return $this->executeQuery($sql);
    }

    public function delete(string $table, array|string $where): bool
    {
        $sql = $this->buildDeleteQuery($table);

        if (!empty($where)) {
            $sql .= " WHERE " . $this->buildWhereQuery($where);
        }

        return $this->executeQuery($sql);
    }

    public function select(
        string $table,
        array|string $fields = "*",
        ?array $join = null,
        null|array|string $where = null,
        ?string $group = null,
        null|array|string $having = null,
        ?string $order = null,
        ?string $limit = null
    ): array {
        $sql = $this->buildSelectQuery($table, $fields);

        if (!empty($join)) {
            $sql .= " " . $this->buildJoinQuery($join);
        }

        if (!empty($where)) {
            $sql .= " WHERE " . $this->buildWhereQuery($where);
        }

        if (!empty($group)) {
            $sql .= " GROUP BY $group";
        }

        if (!empty($having)) {
            $sql .= " HAVING " . $this->buildWhereQuery($having);
        }

        if (!empty($order)) {
            $sql .= " ORDER BY $order";
        }

        if (!empty($limit)) {
            $sql .= " LIMIT $limit";
        }

        return $this->executeQuery($sql);
    }

    public function getTables(): array
    {
        return $this->driverAdapter->getTables($this, $this->dataConn);
    }

    public function getDetTable(string $table): array
    {
        if (!in_array($table, $this->getTables())) {
            return [];
        }

        return $this->driverAdapter->getDetTable($this, $table);
    }

    public function existTable(string $table): bool
    {
        return in_array($table, $this->getTables());
    }

    public function existField(string $table, string $field): bool
    {
        $fields = array_column($this->getDetTable($table), "name");
        return in_array($field, $fields);
    }

    public function setSystemIdentifier(array $data): bool
    {
        return $this->driverAdapter->setSystemIdentifier($this->conn, $data);
    }

    private function makeDriverAdapter(string $driver): PdoDriverAdapter
    {
        return match (strtolower($driver)) {
            "sqlite" => new SqlitePdoDriver(),
            "mysql" => new MysqlPdoDriver(),
            "pgsql" => new PgsqlPdoDriver(),
            default => new PgsqlPdoDriver(),
        };
    }

    public function exists(string $table, array|string $where): bool
    {
        $whereSql = $where ? self::buildWhereQuery($where) : '';
        $sql = "SELECT EXISTS(SELECT 1 FROM $table" . ($whereSql ? " WHERE $whereSql" : "") . ") AS exists";
        $res = $this->executeQuery($sql);
        return !empty($res) && ($res[0]["exists"] ?? $res[0]["EXISTS"] ?? false);
    }

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
    ): array|bool {
        if ($exists && !empty($from)) {
            return $this->exists($from, $where);
        }

        if (!empty($query)) {
            $result = $this->executeQuery($query, $params);
            return $returnFirst ? $result[0] : $result;
        }

        if (!empty($select)) {
            $result = $this->select(
                fields: $select,
                table: $from,
                join: $join,
                where: $where,
                group: $group,
                having: $having,
                order: $order,
                limit: $limit
            );
            return $returnFirst ? $result[0] : $result;
        }

        if (!empty($insert)) {
            return $this->insert(
                data: $insert,
                table: $into,
                returning: $returning
            );
        }

        if (!empty($update)) {
            return $this->update(
                table: $update,
                data: $set,
                where: $where
            );
        }

        if (!empty($delete)) {
            return $this->delete(
                table: $delete,
                where: $where
            );
        }

        return false;
    }
}

