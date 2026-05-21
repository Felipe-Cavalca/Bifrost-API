<?php

namespace Bifrost\Core;

$composerAutoloadCandidates = [
    dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php",
    dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php",
];

foreach ($composerAutoloadCandidates as $composerAutoload) {
    if (is_readable($composerAutoload)) {
        $loader = require_once $composerAutoload;

        if ($loader instanceof \Composer\Autoload\ClassLoader) {
            $apcuEnabled = filter_var(getenv('BFR_API_CACHE_APCU_ENABLED'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            if ($apcuEnabled === null || $apcuEnabled === true) {
                $loader->setApcuPrefix(getenv('BFR_API_CACHE_APCU_PREFIX') ?: 'bifrost_autoload');
            }
        }
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
        if (!str_starts_with($className, $prefix)) {
            return false;
        }

        $className = substr($className, strlen($prefix));
        $relativeFile = str_replace("\\", DIRECTORY_SEPARATOR, $className) . ".php";
        $file = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $relativeFile;
        if (is_readable($file)) {
            require_once $file;
            return true;
        }

        $lowercaseFile = dirname($file) . DIRECTORY_SEPARATOR . lcfirst(basename($file));
        if (is_readable($lowercaseFile)) {
            require_once $lowercaseFile;
            return true;
        }

        return false;
    }
);
