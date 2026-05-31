<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

use Bifrost\Framework\Application;

interface Extension
{
    public function register(Application $application): void;
}
