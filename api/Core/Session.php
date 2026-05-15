<?php

namespace Bifrost\Core;

class Session
{
    private static array $data = [];

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            $this->ensureSavePath();
            session_start();
        }

        if (!isset($_SESSION['session_instance'])) {
            $_SESSION['session_instance'] = [
                'data' => []
            ];
        }

        self::$data = &$_SESSION['session_instance']['data'];
    }

    public function __destruct()
    {
        if(empty(self::$data)) {
            $this->destroy();
        }
    }

    public function __toString(): string
    {
        return json_encode(self::$data);
    }

    public function __set($name, $value): void
    {
        self::$data[$name] = $value;
    }

    public function __get($name): mixed
    {
        return self::$data[$name] ?? null;
    }

    public function __isset($name): bool
    {
        return isset(self::$data[$name]);
    }

    public function __unset($name): void
    {
        unset(self::$data[$name]);
    }

    public function destroy(): void
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    private function ensureSavePath(): void
    {
        $config = SessionConfig::fromRuntime();

        if (!$config->shouldEnsureSavePath()) {
            return;
        }

        $savePath = $config->resolvedSavePath();

        if ($savePath === null || is_dir($savePath)) {
            return;
        }

        if (!mkdir($savePath, 0775, true) && !is_dir($savePath)) {
            throw new \RuntimeException('Unable to create session save path.');
        }
    }
}
