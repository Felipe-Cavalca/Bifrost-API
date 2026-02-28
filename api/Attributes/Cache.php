<?php

namespace Bifrost\Attributes;

use Attribute;
use Bifrost\Core\Cache as CoreCache;
use Bifrost\Core\Get;
use Bifrost\Core\Post;
use Bifrost\Core\Session;
use Bifrost\Interface\Attribute as AttributesInterface;
use Bifrost\Interface\AttributeAfter;
use Bifrost\Interface\AttributeBefore;
use Bifrost\Interface\Responseable;

/**
 * Cache constructor.
 *
 * @param int $time Tempo em segundos que o cache ficará salvo.
 * @param array $fieldsSession Campos da sessão que serão levados em conta ao gerar a chave.
 * @param array $extra Array de valores extras utilizados para compor a chave.
 * @return void
 */
#[Attribute]
class Cache implements AttributesInterface, AttributeBefore, AttributeAfter
{

    private string $key;
    private int $time;
    public function __construct(...$parms)
    {
        $post = new Post();
        $get = new Get();

        $dataKey = [
            "METHOD" => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            "POST" => (string) $post,
            "GET" => (string) $get,
            "SESSION" => $this->getFieldsSession($parms[1] ?? []),
        ];

        if (!empty($parms[2]) && is_array($parms[2])) {
            $dataKey["EXTRA"] = serialize($parms[2]);
        }

        $this->key = serialize($dataKey);
        $this->time = $parms[0];
    }

    public function before(): null|Responseable
    {
        if (CoreCache::exists($this->key)) {
            return CoreCache::get($this->key);
        }
        return null;
    }

    public function after(Responseable $response): void
    {
        CoreCache::set($this->key, $response, $this->time);
    }

    public function getOptions(): array
    {
        return ["cache" => [
            "seconds" => $this->time,
        ]];
    }

    private function getFieldsSession(array $fieldsSession): string
    {
        $session = new Session();
        $fields = [];
        foreach ($fieldsSession as $field) {
            $fields[$field] = (string) $session->$field;
        }
        return serialize($fields);
    }
}
