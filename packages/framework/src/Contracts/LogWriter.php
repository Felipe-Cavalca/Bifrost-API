<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

/**
 * Contrato generico para escritores de log.
 */
interface LogWriter
{
    /**
     * Persiste uma entrada de log estruturada.
     *
     * @param array<string, mixed> $entry Entrada de log serializavel.
     */
    public function write(array $entry): void;
}
