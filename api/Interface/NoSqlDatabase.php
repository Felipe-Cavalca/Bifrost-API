<?php

namespace Bifrost\Interface;

interface NoSqlDatabase
{
    public function insertOne(string $collection, array $document): void;
}
