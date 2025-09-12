<?php

namespace Bifrost\Attributes;

use Attribute;
use Bifrost\Class\HttpResponse;
use Bifrost\Core\Database;
use Bifrost\Interface\Attribute as InterfaceAttribute;
use Bifrost\Interface\AttributeAfter;
use Bifrost\Interface\AttributeBefore;
use Bifrost\Interface\Responseable;

#[Attribute]
class Transaction implements InterfaceAttribute, AttributeBefore, AttributeAfter
{

    public function __construct(...$response) {}

    public function before(): ?Responseable
    {
        $database = new Database();
        $database->begin();

        return null;
    }

    public function after(Responseable $response): void
    {
        if ($response instanceof HttpResponse) {
            $database = new Database();
            if ($response->status->isSuccess()) {
                $database->save();
            } else {
                $database->rollback();
            }
        }
        return;
    }

    public function getOptions(): array
    {
        return [];
    }
}
