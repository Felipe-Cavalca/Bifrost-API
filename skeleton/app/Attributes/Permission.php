<?php

declare(strict_types=1);

namespace App\Attributes;

use Attribute;
use Bifrost\Framework\Container;
use Bifrost\Framework\Contracts\BeforeRequestAttribute;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;

/**
 * Exemplo didatico de permissao executada antes de uma action do controller.
 *
 * Este arquivo existe para mostrar onde colocar attributes especificos da
 * aplicacao. Em um projeto real, substitua a leitura do header por um service
 * de autenticacao resolvido pelo Container.
 *
 * Use em uma action:
 *
 * #[Permission('documents.read')]
 * public function index(Request $request): Response
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Permission implements BeforeRequestAttribute
{
    /**
     * @param string $required Permissao exigida para executar a action.
     */
    public function __construct(private readonly string $required)
    {
    }

    /**
     * Interrompe a request com HTTP 403 quando a permissao nao foi concedida.
     *
     * O Container permite buscar services da aplicacao sem acoplar o framework
     * a uma estrategia especifica de autenticacao.
     */
    public function before(Request $request, Container $container): ?Response
    {
        if ($request->header('X-App-Permission') === $this->required) {
            return null;
        }

        return Response::json(
            payload: ['message' => 'Permissao insuficiente.'],
            status: 403
        );
    }

    /**
     * Expoe metadados que podem ser lidos por OPTIONS ou pela documentacao.
     *
     * @return array{permission: string}
     */
    public function options(): array
    {
        return ['permission' => $this->required];
    }
}
