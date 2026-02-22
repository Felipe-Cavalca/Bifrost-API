<?php

namespace Bifrost\Integration\Database\Driver;

use Bifrost\Integration\Database\PdoDatabase;
use PDO;

interface PdoDriverAdapter
{
    public function connect(array $dataConn): PDO;

    public function getTables(PdoDatabase $database, array $dataConn): array;

    public function getDetTable(PdoDatabase $database, string $table): array;

    public function setSystemIdentifier(PDO $connection, array $data): bool;
}
