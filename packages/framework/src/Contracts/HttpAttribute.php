<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

/**
 * Contrato base para attributes HTTP que expõem metadados do endpoint.
 */
interface HttpAttribute
{
    /**
     * @return array<string, mixed> Metadados usados por OPTIONS ou documentacao.
     */
    public function options(): array;
}
