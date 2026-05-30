<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

use JsonSerializable;

/**
 * Contrato para objetos que podem ser retornados diretamente por controllers.
 *
 * A implementacao deve expor somente os dados seguros para a resposta HTTP.
 * O framework serializa o retorno como JSON sem inspecionar propriedades
 * internas do objeto.
 */
interface Responseable extends JsonSerializable
{
    /**
     * Retorna uma representacao compativel com JSON.
     *
     * @return mixed Dados seguros para exposicao na resposta HTTP.
     */
    public function jsonSerialize(): mixed;
}
