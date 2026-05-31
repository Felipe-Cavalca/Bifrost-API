<?php

declare(strict_types=1);

namespace Bifrost\Framework\Attributes;

use Attribute;
use Bifrost\Framework\Contracts\RequestValidatorAttribute;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;

#[Attribute(Attribute::TARGET_METHOD)]
/**
 * Restringe os metodos HTTP aceitos por uma action de controller.
 *
 * Use em endpoints que devem responder apenas a metodos especificos.
 * Quando o metodo recebido nao estiver na lista, retorna 405 com header Allow.
 *
 * Exemplo: #[Method('GET', 'POST')]
 */
final class Method implements RequestValidatorAttribute
{
    /** @var list<string> */
    private array $methods;

    /**
     * @param string ...$methods Metodos HTTP aceitos, como GET, POST, PUT, PATCH ou DELETE.
     */
    public function __construct(string ...$methods)
    {
        $this->methods = array_map('strtoupper', $methods);
    }

    /**
     * Valida o metodo da request atual.
     *
     * @return Response|null Retorna null quando a request e valida, ou uma resposta 405 quando falha.
     */
    public function validate(Request $request): ?Response
    {
        if (in_array($request->method(), $this->methods, true)) {
            return null;
        }

        return Response::json(
            payload: ['message' => sprintf('Method %s is not allowed for this endpoint.', $request->method())],
            status: 405,
            headers: ['Allow' => implode(', ', $this->methods)]
        );
    }

    /**
     * @return array{methods: list<string>} Metadados expostos para OPTIONS/documentacao do endpoint.
     */
    public function options(): array
    {
        return ['methods' => $this->methods];
    }
}
