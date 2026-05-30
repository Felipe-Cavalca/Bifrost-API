<?php

declare(strict_types=1);

namespace Bifrost\Extension\Redis\Contracts;

/**
 * Cliente Redis usado pelas extensoes Bifrost.
 *
 * Implementacoes futuras podem rotear leitura, escrita ou filas para conexoes diferentes.
 */
interface RedisClient
{
    /**
     * Le uma chave Redis.
     */
    public function get(string $key): string|false;

    /**
     * Grava uma chave Redis sem TTL.
     */
    public function set(string $key, string $value): bool;

    /**
     * Grava uma chave Redis com TTL em segundos.
     */
    public function setex(string $key, int $ttlSeconds, string $value): bool;

    /**
     * Remove uma ou mais chaves Redis.
     */
    public function del(string ...$keys): int|false;

    /**
     * Adiciona um valor ao fim de uma lista Redis.
     */
    public function rPush(string $key, string $value): int|false;

    /**
     * Remove e retorna o primeiro valor de uma lista Redis.
     */
    public function lPop(string $key): string|false;
}
