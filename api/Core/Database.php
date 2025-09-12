<?php

namespace Bifrost\Core;

use Bifrost\Class\HttpResponse;
use Bifrost\Core\AppError;
use Bifrost\Core\Functions;
use Bifrost\Core\Settings;
use Bifrost\Interface\Insertable;
use PDO;
use PDOException;

class Database
{
    private array $drivers = [
        "sqlite" => "sqlite",
        "mysql" => "mysql",
        "pgsql" => "pgsql"
    ];
    private static array $connections = [];
    private static ?Settings $settings = null;
    private PDO $conn;

    public function __construct(?string $databaseName = null)
    {
        self::$settings = new Settings();
        $this->conn = self::$connections[$databaseName] ?? $this->conn($databaseName);
        self::$connections[$databaseName] = $this->conn;
    }

    public function __get(string $name): mixed
    {
        switch ($name) {
            case "conn":
            case "connection":
                return $this->conn;
            case "driver":
                return $this->drivers[$this->conn->getAttribute(PDO::ATTR_DRIVER_NAME)];
            case "hasReturning":
                return in_array($this->driver, ["pgsql"]);
            default:
                return null;
        }
    }

    private function conn(?string $databaseName = null): PDO
    {
        $dataConn = self::$settings->getSettingsDatabase($databaseName);

        switch ($dataConn["driver"]) {
            case "sqlite":
                return new PDO("sqlite:" . $dataConn["database"]);
            case "mysql":
                return new PDO(
                    "mysql:host={$dataConn["host"]}:{$dataConn["port"]};dbname={$dataConn["database"]};charset=utf8",
                    $dataConn["username"],
                    $dataConn["password"]
                );
            case "pgsql":
            default:
                $pdo = new PDO(
                    "pgsql:host={$dataConn["host"]};port={$dataConn["port"]};dbname={$dataConn["database"]}",
                    $dataConn["username"],
                    $dataConn["password"]
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo;
        }
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
        $returning = $this->hasReturning ? $returning : "";

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
        switch ($this->driver) {
            case "pgsql":
                $query = $this->executeQuery("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public';");
                $tables = array_column($query, 'table_name');
                break;
            case "sqlite":
                $query = $this->executeQuery("SELECT name FROM sqlite_master WHERE type='table'");
                $tables = array_column($query, 'name');
                break;
            case "mysql":
                $query = $this->executeQuery("SHOW TABLES");
                $tables = array_column($query, 'Tables_in_' . self::$settings->database["database"]);
                break;
            default:
                $tables = [];
        }

        return $tables;
    }

    public function getDetTable(string $table): array
    {
        if (!in_array($table, $this->getTables())) {
            return [];
        }

        $fields = [];

        switch ($this->driver) {
            case "sqlite":
                $query = $this->executeQuery("PRAGMA table_info({$table})");
                break;
            case "pgsql":
                $query = $this->executeQuery("SELECT column_name AS Field, data_type AS Type, is_nullable AS Null, column_default AS Default,
                                  (SELECT EXISTS (SELECT 1 FROM information_schema.table_constraints tc
                                  JOIN information_schema.key_column_usage kcu
                                  ON tc.constraint_name = kcu.constraint_name
                                  WHERE tc.table_name = '{$table}' AND kcu.column_name = c.column_name AND tc.constraint_type = 'PRIMARY KEY')) AS pk
                                  FROM information_schema.columns c
                                  WHERE table_name = '{$table}'");
            case "mysql":
                $query = $this->executeQuery("DESC {$table}");
                break;
            default:
                $query = [];
        }

        foreach ($query as $field) {
            $fields[] = [
                "name" => $field["field"] ?? $field["Field"] ?? $field["name"],
                "type" => $field["type"] ?? $field["Type"] ?? $field["type"],
                "null" => ($field["null"] ?? $field["Null"] ?? $field["notnull"]) == "YES",
                "default" => isset($field["default"]) ? $field["default"] : (isset($field["Default"]) ? $field["Default"] : (isset($field["dflt_value"]) ? $field["dflt_value"] : null)),
                "pk" => $field["pk"] ?? $field["Extra"] == "auto_increment" ?? $field["pk"] == 1
            ];
        }

        return $fields;
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
        if ($this->driver == "pgsql") {
            $systemIdentifier = json_encode($data);
            return $this->conn->exec("SET bifrost.system_identifier = '$systemIdentifier'");
        }

        return false;
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
