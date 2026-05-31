<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

/**
 * Contrato para stores de cache usados por extensoes do Bifrost.
 */
interface CacheStore
{
    /**
     * Recupera um valor do cache ou null quando nao existir.
     */
    public function get(string $key): mixed;

    /**
     * Armazena um valor no cache.
     *
     * @param int|null $ttlSeconds Tempo de vida em segundos. Null usa o padrao do provider.
     */
    public function set(string $key, mixed $value, ?int $ttlSeconds = null): void;

    /**
     * Remove uma chave do cache.
     */
    public function delete(string $key): void;
}
