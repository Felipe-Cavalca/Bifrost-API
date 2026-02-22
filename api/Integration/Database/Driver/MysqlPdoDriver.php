<?php

namespace Bifrost\Integration\Database\Driver;

use Bifrost\Integration\Database\PdoDatabase;
use PDO;

class MysqlPdoDriver implements PdoDriverAdapter
{
    public function connect(array $dataConn): PDO
    {
        return new PDO(
            "mysql:host={$dataConn["host"]}:{$dataConn["port"]};dbname={$dataConn["database"]};charset=utf8",
            $dataConn["username"],
            $dataConn["password"]
        );
    }

    public function getTables(PdoDatabase $database, array $dataConn): array
    {
        $query = $database->executeQuery("SHOW TABLES");

        return array_column($query, "Tables_in_" . $dataConn["database"]);
    }

    public function getDetTable(PdoDatabase $database, string $table): array
    {
        $query = $database->executeQuery("DESC {$table}");

        return $this->normalizeFields($query);
    }

    public function setSystemIdentifier(PDO $connection, array $data): bool
    {
        return false;
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
