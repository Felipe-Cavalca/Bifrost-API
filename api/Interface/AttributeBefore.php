<?php

namespace Bifrost\Interface;

use Bifrost\Interface\Attribute;
use Bifrost\Interface\Responseable;

interface AttributeBefore extends Attribute
{
    /**
     * Executa uma ação antes do processamento principal.
     * @return Responseable|null Retorna um objeto Responseable para interromper o fluxo ou null para continuar.
     */
    public function before(): null|Responseable;
}
