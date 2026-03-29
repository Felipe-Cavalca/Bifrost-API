<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Autoload.php';

require_once __DIR__ . '/../DataTypes/base64.php';
require_once __DIR__ . '/../DataTypes/filePath.php';
require_once __DIR__ . '/../DataTypes/url.php';
require_once __DIR__ . '/../Interface/PdoDriverAdapter.php';

use Bifrost\Core\Get;
use Bifrost\Core\Post;
use Bifrost\Core\Session;
use Bifrost\Integration\Database\PdoDatabase;

putenv('BFR_API_DISPLAY_ERRORS=0');
putenv('BFR_API_SESSION_SAVE_HANDLER=files');
putenv('BFR_API_SESSION_SAVE_PATH=/tmp');
putenv('BFR_API_SQL_DRIVER=sqlite');
putenv('BFR_API_SQL_DATABASE=' . sys_get_temp_dir() . '/bifrost-api-phpunit.sqlite');
putenv('BFR_API_SQL_USER');
putenv('BFR_API_SQL_PASSWORD');
putenv('BFR_API_SQL_HOST');
putenv('BFR_API_SQL_PORT');
putenv('BFR_API_REDIS_HOST');
putenv('BFR_API_REDIS_PORT');
putenv('BFR_API_APCU_ENABLED=1');
putenv('BFR_API_APCU_TTL=3600');
putenv('BFR_API_REDIS_QUEUE');
putenv('BFR_API_S3_BUCKET');
putenv('BFR_API_S3_REGION');
putenv('BFR_API_S3_KEY');
putenv('BFR_API_S3_SECRET');
putenv('BFR_API_S3_ENDPOINT');
putenv('BFR_API_S3_PATH_STYLE');

$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$_GET = [];
$_POST = [];
$_SESSION = [];

function bifrost_sqlite_path(): string
{
    return (string) getenv('BFR_API_SQL_DATABASE');
}

function bifrost_reset_pdo_connections(): void
{
    $property = new ReflectionProperty(PdoDatabase::class, 'connections');
    $property->setAccessible(true);
    $property->setValue(null, []);
}

function bifrost_reset_database(): void
{
    $path = bifrost_sqlite_path();

    bifrost_reset_pdo_connections();

    if (is_file($path)) {
        unlink($path);
    }

    $pdo = new PDO('sqlite:' . $path);
    $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, active INTEGER, uuid TEXT)');
    $pdo->exec("INSERT INTO users (name, email, active, uuid) VALUES ('Alice', 'alice@example.com', 1, '123e4567-e89b-12d3-a456-426614174000')");
    $pdo->exec("INSERT INTO users (name, email, active, uuid) VALUES ('Bob', 'bob@example.com', 0, '123e4567-e89b-12d3-a456-426614174001')");
}

function bifrost_reset_get(array $data = []): Get
{
    $_GET = $data;
    return new Get();
}

function bifrost_set_post_data(array|object|null $data): Post
{
    $_POST = [];
    $post = new Post();

    $property = new ReflectionProperty(Post::class, 'data');
    $property->setAccessible(true);
    $property->setValue(null, is_array($data) ? (object) $data : $data);

    return $post;
}

function bifrost_reset_session(array $data = []): Session
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    $_SESSION = [];

    $session = new Session();
    foreach ($data as $key => $value) {
        $session->$key = $value;
    }

    return $session;
}
