<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

use PDO;

interface DatabaseConnectionFactory
{
    public function connection(?string $name = null): PDO;
}
