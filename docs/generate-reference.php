<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$packagesDir = $root . '/packages';
$outputDir = $root . '/docs/html/referencia';

ensureDirectory($outputDir);

$classes = [];

foreach (glob($packagesDir . '/*/src/**/*.php') ?: [] as $file) {
    collectClass($file, $classes, $root);
}

foreach (glob($packagesDir . '/*/src/*.php') ?: [] as $file) {
    collectClass($file, $classes, $root);
}

ksort($classes);

$shortNameIndex = [];
foreach ($classes as $fqcn => $class) {
    $shortNameIndex[$class['shortName']] = $fqcn;
}

writeFile($outputDir . '/index.html', renderReferenceIndex($classes));

foreach ($classes as $fqcn => $class) {
    writeFile(
        $outputDir . '/' . classSlug($fqcn) . '.html',
        renderClassPage($class, $classes, $shortNameIndex)
    );
}

function collectClass(string $file, array &$classes, string $root): void
{
    $source = file_get_contents($file);
    if ($source === false) {
        return;
    }

    if (!preg_match('/namespace\s+([^;]+);/', $source, $namespaceMatch)) {
        return;
    }

    if (!preg_match('/\b(final\s+|abstract\s+|readonly\s+)*\b(class|interface|enum)\s+([A-Za-z_][A-Za-z0-9_]*)/', $source, $classMatch)) {
        return;
    }

    $namespace = trim($namespaceMatch[1]);
    $kind = $classMatch[2];
    $shortName = $classMatch[3];
    $fqcn = $namespace . '\\' . $shortName;
    $package = packageName($file);
    $relativePath = str_replace('\\', '/', substr($file, strlen($root) + 1));

    $classes[$fqcn] = [
        'fqcn' => $fqcn,
        'shortName' => $shortName,
        'namespace' => $namespace,
        'kind' => $kind,
        'package' => $package,
        'path' => $relativePath,
        'line' => lineNumber($source, $classMatch[0]),
        'description' => classDescription($source, $classMatch[0]),
        'methods' => publicMethods($source),
    ];
}

function packageName(string $file): string
{
    if (preg_match('#packages[\\\\/]([^\\\\/]+)#', $file, $match)) {
        return $match[1];
    }

    return 'desconhecido';
}

function classDescription(string $source, string $needle): string
{
    $position = strpos($source, $needle);
    if ($position === false) {
        return '';
    }

    $before = substr($source, 0, $position);
    if (!preg_match('/\/\*\*((?:(?!\/\*\*).)*?)\*\/\s*$/s', $before, $match)) {
        return '';
    }

    return cleanDoc($match[1]);
}

function publicMethods(string $source): array
{
    $methods = [];
    $pattern = '/public\s+(static\s+)?function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\((.*?)\)\s*(?::\s*([^{;]+))?/s';

    if (!preg_match_all($pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
        return $methods;
    }

    foreach ($matches as $match) {
        $docRaw = methodDoc($source, $match[0][1]);
        $paramsRaw = trim($match[3][0]);
        $return = returnDoc($docRaw);
        $returnType = $match[2][0] === '__construct'
            ? 'void'
            : normalizeType($return['type'] !== '' ? $return['type'] : trim($match[4][0] ?? 'mixed'));
        $methods[] = [
            'name' => $match[2][0],
            'static' => trim($match[1][0] ?? '') !== '',
            'paramsRaw' => $paramsRaw,
            'returnType' => $returnType === '' ? 'mixed' : $returnType,
            'line' => lineNumber($source, $match[0][0]),
            'description' => cleanDoc($docRaw),
            'paramDocs' => paramDocs($docRaw),
            'returnDoc' => $return['description'],
            'signature' => methodSignature($match[0][0]),
        ];
    }

    return $methods;
}

function methodDoc(string $source, int $methodPosition): string
{
    $before = substr($source, 0, $methodPosition);

    if (!preg_match('/\/\*\*((?:(?!\/\*\*).)*?)\*\/\s*$/s', $before, $match)) {
        return '';
    }

    return '/**' . $match[1] . '*/';
}

function methodSignature(string $raw): string
{
    $raw = preg_replace('/\/\*\*.*?\*\//s', '', $raw) ?? $raw;
    $raw = preg_replace('/\s+/', ' ', trim($raw)) ?? $raw;
    $raw = rtrim($raw, ' {');

    return trim($raw);
}

function paramDocs(string $doc): array
{
    $params = [];

    if (preg_match_all('/@param\s+(.+?)\s+\$([A-Za-z_][A-Za-z0-9_]*)[ \t]*(.*)$/m', $doc, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $params[$match[2]] = [
                'type' => normalizeType($match[1]),
                'description' => cleanInlineDoc($match[3]),
            ];
        }
    }

    return $params;
}

function returnDoc(string $doc): array
{
    if (!preg_match('/@return\s+(.+)$/m', $doc, $match)) {
        return ['type' => '', 'description' => ''];
    }

    return parseTypeAndDescription(cleanInlineDoc($match[1]));
}

function parseTypeAndDescription(string $text): array
{
    $text = trim($text);
    if ($text === '') {
        return ['type' => '', 'description' => ''];
    }

    $typeEnd = typeEndPosition($text);
    if ($typeEnd >= 0) {
        return [
            'type' => normalizeType(substr($text, 0, $typeEnd + 1)),
            'description' => trim(substr($text, $typeEnd + 1)),
        ];
    }

    if (preg_match('/^([^\s]+)(?:\s+(.*))?$/', $text, $match)) {
        return [
            'type' => normalizeType($match[1]),
            'description' => trim($match[2] ?? ''),
        ];
    }

    return ['type' => normalizeType($text), 'description' => ''];
}

function typeEndPosition(string $text): int
{
    $angleDepth = 0;
    $shapeDepth = 0;
    $last = strlen($text) - 1;

    for ($i = 0; $i <= $last; $i++) {
        $char = $text[$i];

        if ($char === '<') {
            $angleDepth++;
            continue;
        }

        if ($char === '{') {
            $shapeDepth++;
            continue;
        }

        if ($char === '>' && $angleDepth > 0) {
            $angleDepth--;
            continue;
        }

        if ($char === '}' && $shapeDepth > 0) {
            $shapeDepth--;
            continue;
        }

        if (ctype_space($char) && $angleDepth === 0 && $shapeDepth === 0) {
            return $i - 1;
        }
    }

    return $last;
}

function cleanInlineDoc(string $text): string
{
    $text = trim($text);
    $text = preg_replace('#\s*\*/$#', '', $text) ?? $text;

    return trim($text);
}

function cleanDoc(string $doc): string
{
    $doc = preg_replace('#^/\*\*|\*/$#', '', trim($doc)) ?? $doc;
    $lines = preg_split('/\R/', $doc) ?: [];
    $clean = [];

    foreach ($lines as $line) {
        $line = preg_replace('/^\s*\*\s?/', '', $line) ?? $line;
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '@')) {
            continue;
        }

        $clean[] = $line;
    }

    return trim(implode(' ', $clean));
}

function lineNumber(string $source, string $needle): int
{
    $position = strpos($source, $needle);
    if ($position === false) {
        return 1;
    }

    return substr_count(substr($source, 0, $position), "\n") + 1;
}

function renderReferenceIndex(array $classes): string
{
    $byPackage = [];
    foreach ($classes as $class) {
        $byPackage[$class['package']][] = $class;
    }

    ksort($byPackage);

    $body = '<span class="eyebrow">Referência</span><h1>Referência de API</h1>';
    $body .= '<p class="lead">Lista gerada a partir dos métodos públicos em <code>packages/*/src</code>. Use esta seção para descobrir quais funções podem ser chamadas, quais parâmetros elas recebem e quais tipos retornam.</p>';

    foreach ($byPackage as $package => $items) {
        $body .= '<h2>' . e($package) . '</h2><div class="reference-list">';
        foreach ($items as $class) {
            $body .= '<a class="reference-row" href="' . e(classSlug($class['fqcn'])) . '.html">';
            $body .= '<strong>' . e($class['shortName']) . '</strong>';
            $body .= '<span>' . e($class['fqcn']) . '</span>';
            $body .= '</a>';
        }
        $body .= '</div>';
    }

    return layout('Referência de API - Bifrost', $body, '../');
}

function renderClassPage(array $class, array $classes, array $shortNameIndex): string
{
    $body = '<span class="eyebrow">' . e($class['kind']) . '</span>';
    $body .= '<h1>' . e($class['shortName']) . '</h1>';
    $body .= '<p class="lead"><code>' . e($class['fqcn']) . '</code></p>';

    if ($class['description'] !== '') {
        $body .= '<p>' . e($class['description']) . '</p>';
    }

    $body .= '<div class="callout"><p>Fonte: <code>' . e($class['path']) . ':' . $class['line'] . '</code></p></div>';
    $body .= '<h2>Métodos públicos</h2>';

    if ($class['methods'] === []) {
        $body .= '<p>Esta classe não expõe métodos públicos próprios.</p>';
    }

    foreach ($class['methods'] as $method) {
        $body .= '<section class="method">';
        $body .= '<h3 id="' . e($method['name']) . '">' . e($method['name']) . '()</h3>';
        if ($method['description'] !== '') {
            $body .= '<p>' . e($method['description']) . '</p>';
        }
        $body .= '<pre><code class="language-php">' . e($method['signature']) . '</code></pre>';
        $body .= '<p class="source-ref">Fonte: <code>' . e($class['path']) . ':' . $method['line'] . '</code></p>';
        $body .= '<table><thead><tr><th>Item</th><th>Tipo</th><th>Descrição</th></tr></thead><tbody>';

        foreach (methodParams($method['paramsRaw']) as $param) {
            $doc = $method['paramDocs'][$param['name']] ?? null;
            $type = $doc['type'] ?? $param['type'];
            $description = $doc['description'] ?? '';
            $body .= '<tr><td><code>$' . e($param['name']) . '</code></td><td>' . typeLinks($type, $classes, $shortNameIndex) . '</td><td>' . e($description) . '</td></tr>';
        }

        $body .= '<tr><td><strong>retorno</strong></td><td>' . typeLinks($method['returnType'], $classes, $shortNameIndex) . '</td><td>' . e($method['returnDoc']) . '</td></tr>';
        $body .= '</tbody></table></section>';
    }

    $body .= '<div class="next"><a href="index.html">Referência</a><span></span></div>';

    return layout($class['shortName'] . ' - Referência Bifrost', $body, '../');
}

function methodParams(string $raw): array
{
    if ($raw === '') {
        return [];
    }

    $params = [];
    foreach (explode(',', $raw) as $part) {
        $part = trim($part);
        if (!preg_match('/(?:(.*?)\s+)?(?:&|\.\.\.)?\$([A-Za-z_][A-Za-z0-9_]*)/', $part, $match)) {
            continue;
        }

        $params[] = [
            'name' => $match[2],
            'type' => normalizeType(trim($match[1] ?? 'mixed') ?: 'mixed'),
        ];
    }

    return $params;
}

function normalizeType(string $type): string
{
    $type = trim($type);
    $type = preg_replace('/\b(private|protected|public|readonly|static)\b\s*/', '', $type) ?? $type;
    $type = preg_replace('/\s+/', ' ', $type) ?? $type;
    $type = str_replace(', ', ',', $type);

    return trim($type) === '' ? 'mixed' : trim($type);
}

function typeLinks(string $type, array $classes, array $shortNameIndex): string
{
    $parts = preg_split('/(\||&|\?|,|\s+)/', $type, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
    $html = '';

    foreach ($parts as $part) {
        $clean = trim($part, '[]() ');
        $fqcn = null;

        if (isset($classes[ltrim($clean, '\\')])) {
            $fqcn = ltrim($clean, '\\');
        } elseif (isset($shortNameIndex[$clean])) {
            $fqcn = $shortNameIndex[$clean];
        }

        if ($fqcn !== null) {
            $html .= '<a href="' . e(classSlug($fqcn)) . '.html"><code>' . e($part) . '</code></a>';
            continue;
        }

        $externalUrl = externalTypeUrl($clean);
        if ($externalUrl !== null) {
            $html .= '<a href="' . e($externalUrl) . '" target="_blank" rel="noreferrer"><code>' . e($part) . '</code></a>';
            continue;
        }

        $html .= e($part);
    }

    return $html === '' ? 'mixed' : $html;
}

function externalTypeUrl(string $type): ?string
{
    $type = ltrim($type, '\\');
    $aliases = [
        'AWS\S3\S3Client' => 'Aws\S3\S3Client',
    ];
    $type = $aliases[$type] ?? $type;

    $links = [
        'ArrayAccess' => 'https://www.php.net/manual/pt_BR/class.arrayaccess.php',
        'BackedEnum' => 'https://www.php.net/manual/pt_BR/class.backedenum.php',
        'DateTimeImmutable' => 'https://www.php.net/manual/pt_BR/class.datetimeimmutable.php',
        'DateTimeInterface' => 'https://www.php.net/manual/pt_BR/class.datetimeinterface.php',
        'Exception' => 'https://www.php.net/manual/pt_BR/class.exception.php',
        'InvalidArgumentException' => 'https://www.php.net/manual/pt_BR/class.invalidargumentexception.php',
        'IteratorAggregate' => 'https://www.php.net/manual/pt_BR/class.iteratoraggregate.php',
        'JsonSerializable' => 'https://www.php.net/manual/pt_BR/class.jsonserializable.php',
        'PDO' => 'https://www.php.net/manual/pt_BR/class.pdo.php',
        'PDOStatement' => 'https://www.php.net/manual/pt_BR/class.pdostatement.php',
        'Redis' => 'https://github.com/phpredis/phpredis',
        'Result' => 'https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.Result.html',
        'RuntimeException' => 'https://www.php.net/manual/pt_BR/class.runtimeexception.php',
        'S3Client' => 'https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3Client.html',
        'Stringable' => 'https://www.php.net/manual/pt_BR/class.stringable.php',
        'Throwable' => 'https://www.php.net/manual/pt_BR/class.throwable.php',
        'Traversable' => 'https://www.php.net/manual/pt_BR/class.traversable.php',
        'UnexpectedValueException' => 'https://www.php.net/manual/pt_BR/class.unexpectedvalueexception.php',
        'BulkWrite' => 'https://www.php.net/manual/pt_BR/class.mongodb-driver-bulkwrite.php',
        'Manager' => 'https://www.php.net/manual/pt_BR/class.mongodb-driver-manager.php',
        'WriteConcern' => 'https://www.php.net/manual/pt_BR/class.mongodb-driver-writeconcern.php',
        'Aws\Result' => 'https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.Result.html',
        'Aws\S3\S3Client' => 'https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3Client.html',
        'MongoDB\Driver\BulkWrite' => 'https://www.php.net/manual/pt_BR/class.mongodb-driver-bulkwrite.php',
        'MongoDB\Driver\Manager' => 'https://www.php.net/manual/pt_BR/class.mongodb-driver-manager.php',
        'MongoDB\Driver\WriteConcern' => 'https://www.php.net/manual/pt_BR/class.mongodb-driver-writeconcern.php',
    ];

    return $links[$type] ?? null;
}

function layout(string $title, string $body, string $assetPrefix): string
{
    return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . e($title) . '</title>
    <link rel="stylesheet" href="' . $assetPrefix . 'assets/styles.css">
</head>
<body>
    <div class="mobile-bar"><strong>Bifrost</strong><button class="menu-button" data-menu-toggle>Menu</button></div>
    <div class="layout">
        <aside class="sidebar">
            <a class="brand" href="' . $assetPrefix . 'index.html"><span class="brand-mark">B</span><span><span class="brand-name">Bifrost</span><span class="brand-subtitle">Framework Composer</span></span></a>
            <button class="theme-button" data-theme-toggle>Tema escuro</button>
            <nav>
                <section class="nav-section"><p class="nav-title">Comece aqui</p><a href="' . $assetPrefix . 'index.html" class="nav-link">Visão geral</a><a href="' . $assetPrefix . '01-instalacao.html" class="nav-link">Instalação</a><a href="' . $assetPrefix . '02-primeiros-passos.html" class="nav-link">Primeiros passos</a></section>
                <section class="nav-section"><p class="nav-title">Usando o framework</p><a href="' . $assetPrefix . '03-rotas-e-controllers.html" class="nav-link">Rotas e controllers</a><a href="' . $assetPrefix . '04-request-response.html" class="nav-link">Request e response</a><a href="' . $assetPrefix . '05-datatypes.html" class="nav-link">DataTypes</a></section>
                <section class="nav-section"><p class="nav-title">Módulos</p><a href="' . $assetPrefix . 'modulos/index.html" class="nav-link">Todos os módulos</a><a href="' . $assetPrefix . 'modulos/cache.html" class="nav-link">Cache</a><a href="' . $assetPrefix . 'modulos/redis.html" class="nav-link">Redis</a><a href="' . $assetPrefix . 'modulos/database.html" class="nav-link">Banco de dados</a><a href="' . $assetPrefix . 'modulos/storage.html" class="nav-link">Storage</a><a href="' . $assetPrefix . 'modulos/logs.html" class="nav-link">Logs</a><a href="' . $assetPrefix . 'modulos/filas.html" class="nav-link">Filas</a></section>
                <section class="nav-section"><p class="nav-title">Referência</p><a href="' . $assetPrefix . 'referencia/index.html" class="nav-link">API pública</a></section>
                <section class="nav-section"><p class="nav-title">Entrega</p><a href="' . $assetPrefix . '06-deploy.html" class="nav-link">Docker e deploy</a></section>
            </nav>
        </aside>
        <main class="content"><article class="article">' . $body . '</article></main>
    </div>
    <script src="' . $assetPrefix . 'assets/app.js"></script>
</body>
</html>';
}

function classSlug(string $fqcn): string
{
    return strtolower(str_replace('\\', '-', $fqcn));
}

function ensureDirectory(string $dir): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

function writeFile(string $path, string $content): void
{
    file_put_contents($path, $content);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
