<?php

namespace Bifrost\Integration\Database\Driver;

use Bifrost\Integration\Database\PdoDatabase;
use PDO;

class PgsqlPdoDriver implements PdoDriverAdapter
{
    public function connect(array $dataConn): PDO
    {
        $pdo = new PDO(
            "pgsql:host={$dataConn["host"]};port={$dataConn["port"]};dbname={$dataConn["database"]}",
            $dataConn["username"],
            $dataConn["password"]
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public function getTables(PdoDatabase $database, array $dataConn): array
    {
        $query = $database->executeQuery(
            "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public';"
        );

        return array_column($query, "table_name");
    }

    public function getDetTable(PdoDatabase $database, string $table): array
    {
        $query = $database->executeQuery("SELECT column_name AS Field, data_type AS Type, is_nullable AS Null, column_default AS Default,
                                  (SELECT EXISTS (SELECT 1 FROM information_schema.table_constraints tc
                                  JOIN information_schema.key_column_usage kcu
                                  ON tc.constraint_name = kcu.constraint_name
                                  WHERE tc.table_name = '{$table}' AND kcu.column_name = c.column_name AND tc.constraint_type = 'PRIMARY KEY')) AS pk
                                  FROM information_schema.columns c
                                  WHERE table_name = '{$table}'");

        return $this->normalizeFields($query);
    }

    public function setSystemIdentifier(PDO $connection, array $data): bool
    {
        $systemIdentifier = json_encode($data);

        return (bool) $connection->exec("SET bifrost.system_identifier = '$systemIdentifier'");
    }

    private function normalizeFields(array $query): array
    {
        $fields = [];
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
}
