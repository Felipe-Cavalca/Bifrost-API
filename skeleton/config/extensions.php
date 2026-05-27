<?php

declare(strict_types=1);

use Bifrost\Extension\CacheApcu\ApcuCacheExtension;
use Bifrost\Extension\CacheRedis\RedisCacheExtension;
use Bifrost\Extension\DatabaseMySql\MySqlExtension;
use Bifrost\Extension\DatabasePostgreSql\PostgreSqlExtension;
use Bifrost\Extension\QueueRedis\RedisQueueExtension;
use Bifrost\Framework\Contracts\Extension;

/** @var list<Extension> $extensions */
$extensions = [];

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

return $extensions;
