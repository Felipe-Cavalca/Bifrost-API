<?php

declare(strict_types=1);

namespace Bifrost\Framework\Attributes;

use Attribute;
use Bifrost\Framework\Contracts\HttpAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
/**
 * Adiciona metadados livres a uma action HTTP.
 *
 * Use para expor detalhes de documentacao, tags, resumo ou qualquer informacao
 * consumida por ferramentas que leem attributes via OPTIONS.
 *
 * Exemplo: #[Details(['summary' => 'Lista usuarios', 'tags' => ['usuarios']])]
 */
final class Details implements HttpAttribute
{
    /**
     * @param array<string, mixed> $details Metadados livres do endpoint.
     */
    public function __construct(private readonly array $details)
    {
    }

    /**
     * @return array<string, mixed> Metadados informados no attribute.
     */
    public function options(): array
    {
        return $this->details;
    }
}
