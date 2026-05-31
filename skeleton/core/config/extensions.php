<?php

declare(strict_types=1);

use Bifrost\Extension\CacheApcu\ApcuCacheExtension;
use Bifrost\Extension\CacheRedis\RedisCacheExtension;
use Bifrost\Extension\DatabaseMySql\MySqlExtension;
use Bifrost\Extension\DatabasePostgreSql\PostgreSqlExtension;
use Bifrost\Extension\LogFile\FileLogExtension;
use Bifrost\Extension\LogMongoDb\MongoLogExtension;
use Bifrost\Extension\LogStdout\StdoutLogExtension;
use Bifrost\Extension\QueueRedis\RedisQueueExtension;
use Bifrost\Framework\Contracts\Extension;

/** @var list<Extension> $extensions */
$extensions = [];

// Pacotes opcionais nao fazem parte do framework base. Instale apenas o pacote
// usado pelo projeto e selecione o driver correspondente no ambiente.
$cacheDriver = getenv('CACHE_DRIVER') ?: '';
if ($cacheDriver !== '' && !in_array($cacheDriver, ['apcu', 'redis'], true)) {
    throw new RuntimeException('CACHE_DRIVER deve ser apcu ou redis.');
}

if ($cacheDriver === 'redis' && !class_exists(RedisCacheExtension::class)) {
    throw new RuntimeException('Instale bifrost/cache-redis para usar CACHE_DRIVER=redis.');
}

if ($cacheDriver === 'redis') {
    $extensions[] = new RedisCacheExtension([
        'host' => getenv('REDIS_HOST') ?: 'redis',
        'port' => (int) (getenv('REDIS_PORT') ?: 6379),
        'prefix' => getenv('CACHE_PREFIX') ?: 'bifrost:cache:',
    ]);
}

if ($cacheDriver === 'apcu' && !class_exists(ApcuCacheExtension::class)) {
    throw new RuntimeException('Instale bifrost/cache-apcu para usar CACHE_DRIVER=apcu.');
}

if ($cacheDriver === 'apcu') {
    $cacheConfig = [
        'prefix' => getenv('CACHE_PREFIX') ?: 'bifrost:cache:',
    ];
    $cacheTtl = getenv('CACHE_TTL');
    if ($cacheTtl !== false && $cacheTtl !== '') {
        $cacheConfig['ttl'] = (int) $cacheTtl;
    }

    $extensions[] = new ApcuCacheExtension($cacheConfig);
}

if (class_exists(RedisQueueExtension::class)) {
    $extensions[] = new RedisQueueExtension([
        'host' => getenv('REDIS_HOST') ?: 'redis',
        'port' => (int) (getenv('REDIS_PORT') ?: 6379),
        'prefix' => getenv('QUEUE_PREFIX') ?: 'bifrost:queue:',
    ]);
}

$databaseDriver = getenv('DB_DRIVER') ?: '';
if ($databaseDriver !== '' && !in_array($databaseDriver, ['mysql', 'postgresql'], true)) {
    throw new RuntimeException('DB_DRIVER deve ser mysql ou postgresql.');
}

$databaseConfig = [
    'host' => getenv('DB_HOST') ?: $databaseDriver,
    'port' => (int) (getenv('DB_PORT') ?: ($databaseDriver === 'mysql' ? 3306 : 5432)),
    'database' => getenv('DB_DATABASE') ?: 'app',
    'username' => getenv('DB_USERNAME') ?: 'app',
    'password' => getenv('DB_PASSWORD') ?: '',
];

if ($databaseDriver === 'mysql' && !class_exists(MySqlExtension::class)) {
    throw new RuntimeException('Instale bifrost/database-mysql para usar DB_DRIVER=mysql.');
}

if ($databaseDriver === 'mysql') {
    $extensions[] = new MySqlExtension($databaseConfig);
}

if ($databaseDriver === 'postgresql' && !class_exists(PostgreSqlExtension::class)) {
    throw new RuntimeException('Instale bifrost/database-postgresql para usar DB_DRIVER=postgresql.');
}

if ($databaseDriver === 'postgresql') {
    $extensions[] = new PostgreSqlExtension($databaseConfig);
}

$logDriver = getenv('LOG_DRIVER') ?: '';
if ($logDriver !== '' && !in_array($logDriver, ['stdout', 'file', 'mongodb'], true)) {
    throw new RuntimeException('LOG_DRIVER deve ser stdout, file ou mongodb.');
}

if ($logDriver === 'stdout' && !class_exists(StdoutLogExtension::class)) {
    throw new RuntimeException('Instale bifrost/log-stdout para usar LOG_DRIVER=stdout.');
}

if ($logDriver === 'stdout') {
    $extensions[] = new StdoutLogExtension([
        'stream' => getenv('LOG_STREAM') ?: 'stdout',
    ]);
}

if ($logDriver === 'file' && !class_exists(FileLogExtension::class)) {
    throw new RuntimeException('Instale bifrost/log-file para usar LOG_DRIVER=file.');
}

if ($logDriver === 'file') {
    $extensions[] = new FileLogExtension([
        'path' => getenv('LOG_FILE') ?: dirname(__DIR__, 2) . '/storage/logs/app.log',
    ]);
}

if ($logDriver === 'mongodb' && !class_exists(MongoLogExtension::class)) {
    throw new RuntimeException('Instale bifrost/log-mongodb para usar LOG_DRIVER=mongodb.');
}

if ($logDriver === 'mongodb') {
    $extensions[] = new MongoLogExtension([
        'uri' => getenv('MONGO_LOG_URI') ?: null,
        'host' => getenv('MONGO_LOG_HOST') ?: 'mongo',
        'port' => getenv('MONGO_LOG_PORT') ?: '27017',
        'database' => getenv('MONGO_LOG_DATABASE') ?: 'bifrost_logs',
        'collection' => getenv('MONGO_LOG_COLLECTION') ?: 'logs',
        'username' => getenv('MONGO_LOG_USERNAME') ?: null,
        'password' => getenv('MONGO_LOG_PASSWORD') ?: null,
    ]);
}

return $extensions;
