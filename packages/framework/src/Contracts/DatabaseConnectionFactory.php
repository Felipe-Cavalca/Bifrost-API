<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

use PDO;

/**
 * Fabrica conexoes PDO nomeadas para pacotes de banco de dados.
 */
interface DatabaseConnectionFactory
{
    /**
     * Retorna uma conexao PDO.
     *
     * @param string|null $name Nome da conexao. Null usa a conexao padrao.
     */
    public function connection(?string $name = null): PDO;
}
