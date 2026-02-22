<?php

namespace Bifrost\Core;

$composerAutoloadCandidates = [
    dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php",
    dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php",
];

foreach ($composerAutoloadCandidates as $composerAutoload) {
    if (is_readable($composerAutoload)) {
        require_once $composerAutoload;
        break;
    }
}

/**
 * Função responsável por importar classes do sistema.
 *
 * @param string $className O nome da classe a ser importada.
 * @return bool Retorna true se a classe foi importada com sucesso, caso contrário retorna false.
 */
spl_autoload_register(
    function (string $className): bool {
        $prefix = 'Bifrost\\';
        if (strpos($className, $prefix) === 0) {
            $className = substr($className, strlen($prefix));
        }

        $file = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $className) . ".php";
        if (is_readable($file)) {
            require_once $file;
        }

        return true;
    }
);
