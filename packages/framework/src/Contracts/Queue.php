<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

/**
 * Contrato para adaptadores de fila.
 */
interface Queue
{
    /**
     * Publica uma mensagem em uma fila.
     *
     * @param array<string, mixed> $payload Dados serializaveis da mensagem.
     */
    public function push(string $queue, array $payload): void;

    /**
     * Consome uma mensagem da fila.
     *
     * @return array<string, mixed>|null Payload da mensagem ou null quando nao houver item.
     */
    public function pop(string $queue): ?array;
}
