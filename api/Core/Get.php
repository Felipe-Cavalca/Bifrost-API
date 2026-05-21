<?php

namespace Bifrost\Core;

use Bifrost\Enum\Routes;

class Get
{
    private static array $data = [];
    public static string $controller;
    public static string $action;
    public static bool $routeMapped = false;

    public function __construct()
    {
        if ($_GET instanceof Get) {
            return;
        }

        $data = $_GET;
        $path = $data["_controller"] ?? "index";
        $action = empty($data["_action"]) ? "index" : $data["_action"];
        $route = Routes::fromRequest((string) $path);
        self::$routeMapped = $route !== null;

        if ($route) {
            [$controller, $action] = explode("/", $route->value);
        } else {
            $controller = (string) $path;
        }

        self::$controller = $controller;
        self::$action = (string) $action;

        unset($data["_controller"], $data["_action"]);

        self::$data = $data;
        $_GET = $this;
    }

    public function __toString()
    {
        return json_encode(array_merge(self::$data, [
            "_controller" => self::$controller,
            "_action" => self::$action
        ]), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function __get($name)
    {
        switch ($name) {
            case "controller":
                return self::$controller;
            case "action":
                return self::$action;
            case "routeMapped":
                return self::$routeMapped;
            default:
                return self::$data[$name] ?? null;
        }
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case "controller":
                self::$controller = $value;
                break;
            case "action":
                self::$action = $value;
                break;
            default:
                self::$data[$name] = $value;
        }
    }

    public function __isset($name)
    {
        return isset(self::$data[$name]);
    }

    public function __unset($name)
    {
        unset(self::$data[$name]);
    }
}
