<?php

declare(strict_types=1);

namespace App\DataTypes;

use Bifrost\Framework\Contracts\DataType;
use Bifrost\Framework\Contracts\Insertable;
use Bifrost\Framework\Contracts\Responseable;
use InvalidArgumentException;

/**
 * Exemplo de DataType pertencente somente a esta aplicacao.
 *
 * Use DataTypes para valores com regra propria. ProjectCode nao e parte do
 * framework porque cada produto pode definir um formato diferente.
 *
 * Como implementa Insertable, uma camada de persistencia pode converter a
 * instancia para value() antes de executar INSERT, UPDATE ou parametros SQL.
 * Como implementa Responseable, um controller pode retorna-la diretamente.
 */
final readonly class ProjectCode implements DataType, Insertable, Responseable
{
    private function __construct(private string $value)
    {
    }

    /**
     * Cria um codigo validado e normalizado.
     */
    public static function from(mixed $value): static
    {
        if (!self::isValid($value)) {
            throw new InvalidArgumentException('ProjectCode deve usar o formato APP-1234.');
        }

        return new self(strtoupper((string) $value));
    }

    /**
     * Aceita prefixo alfabetico, hifen e quatro digitos.
     */
    public static function isValid(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/^[a-z]+-[0-9]{4}$/i', $value) === 1;
    }

    /**
     * Retorna o valor primitivo usado pela persistencia.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Retorna o valor seguro que sera exposto como JSON.
     */
    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
