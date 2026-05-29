<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

use DateTimeImmutable;

/**
 * Contrato para storage de arquivos independente de provider.
 */
interface Storage
{
    /**
     * Salva um corpo em uma chave/caminho.
     *
     * @param array<string, mixed> $options Opcoes especificas do provider.
     * @return array<string, mixed> Metadados do arquivo salvo.
     */
    public function put(string $key, string $body, array $options = []): array;

    /**
     * Recupera um arquivo por chave/caminho.
     *
     * @param array<string, mixed> $options Opcoes especificas do provider.
     * @return array<string, mixed> Metadados e conteudo conforme implementacao.
     */
    public function get(string $key, array $options = []): array;

    /**
     * Remove um arquivo por chave/caminho.
     *
     * @param array<string, mixed> $options Opcoes especificas do provider.
     * @return array<string, mixed> Metadados da remocao.
     */
    public function delete(string $key, array $options = []): array;

    /**
     * Gera uma URL temporaria para acesso ao arquivo.
     *
     * @param DateTimeImmutable|null $expiresAt Data de expiracao. Null usa o padrao do provider.
     * @param array<string, mixed> $options Opcoes especificas do provider.
     */
    public function temporaryUrl(
        string $key,
        ?DateTimeImmutable $expiresAt = null,
        array $options = []
    ): string;
}
