<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

/**
 * Contrato para objetos de valor com validacao propria.
 *
 * Use DataTypes quando um valor representa conceito do dominio e precisa de
 * validacao, normalizacao ou tipagem forte em mais de um fluxo.
 */
interface DataType
{
    /**
     * Cria o DataType a partir de um valor bruto validado.
     */
    public static function from(mixed $value): static;

    /**
     * Verifica se um valor bruto pode ser aceito pelo DataType.
     */
    public static function isValid(mixed $value): bool;

    /**
     * Retorna o valor primitivo armazenado.
     */
    public function value(): mixed;
}
