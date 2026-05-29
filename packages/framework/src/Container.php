<?php

declare(strict_types=1);

namespace Bifrost\Framework;

use RuntimeException;

/**
 * Container simples de dependencias da aplicacao.
 *
 * Permite registrar factories ou instancias e resolver objetos por identificador.
 */
final class Container
{
    /** @var array<string, callable|object> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    /**
     * Registra uma factory ou objeto para um identificador.
     *
     * @param string $id Identificador pesquisavel, normalmente a FQCN da interface/classe.
     * @param callable|object $binding Factory que recebe o container ou objeto pronto.
     */
    public function bind(string $id, callable|object $binding): void
    {
        $this->bindings[$id] = $binding;
        unset($this->instances[$id]);
    }

    /**
     * Registra uma instancia pronta para um identificador.
     */
    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
        unset($this->bindings[$id]);
    }

    /**
     * Verifica se existe binding ou instancia para o identificador.
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    /**
     * Resolve um objeto registrado no container.
     *
     * @throws RuntimeException Quando o servico nao existe ou nao resolve para objeto.
     */
    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $binding = $this->bindings[$id] ?? null;
        if ($binding === null) {
            throw new RuntimeException("Servico '{$id}' nao foi registrado.");
        }

        $instance = is_callable($binding) ? $binding($this) : $binding;
        if (!is_object($instance)) {
            throw new RuntimeException("Servico '{$id}' deve resolver para um objeto.");
        }

        return $this->instances[$id] = $instance;
    }
}
