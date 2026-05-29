<?php

declare(strict_types=1);

namespace Bifrost\Framework\Attributes;

use Attribute;
use Bifrost\Framework\Contracts\HttpAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
/**
 * Descreve o formato esperado da resposta de uma action HTTP.
 *
 * O schema e livre para permitir documentacao simples ou integracao futura
 * com geradores de contrato.
 *
 * Exemplo: #[Response(['status' => 200, 'body' => ['id' => 'int']])]
 */
final class Response implements HttpAttribute
{
    /**
     * @param array<string, mixed> $schema Schema ou metadados da resposta.
     */
    public function __construct(private readonly array $schema)
    {
    }

    /**
     * @return array{response: array<string, mixed>} Metadados da resposta.
     */
    public function options(): array
    {
        return ['response' => $this->schema];
    }
}
