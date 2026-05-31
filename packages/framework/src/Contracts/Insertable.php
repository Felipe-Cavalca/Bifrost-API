<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

/**
 * Contrato para valores tipados que podem ser persistidos no banco.
 */
interface Insertable
{
    /**
     * Retorna o valor primitivo usado em INSERT, UPDATE e parametros SQL.
     */
    public function value(): string|int|bool|float|null;
}
