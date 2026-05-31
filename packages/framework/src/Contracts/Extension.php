<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

use Bifrost\Framework\Application;

/**
 * Contrato para pacotes que registram servicos, rotas ou configuracoes na aplicacao.
 */
interface Extension
{
    /**
     * Registra a extensao na aplicacao Bifrost.
     */
    public function register(Application $application): void;
}
