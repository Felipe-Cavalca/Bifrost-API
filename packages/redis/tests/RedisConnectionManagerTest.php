<?php

declare(strict_types=1);

use Bifrost\Extension\Redis\Contracts\RedisClient;
use Bifrost\Extension\Redis\Contracts\RedisConnectionFactory;
use Bifrost\Extension\Redis\RedisConfig;
use Bifrost\Extension\Redis\RedisConnectionManager;
use Bifrost\Extension\Redis\RedisExtension;
use Bifrost\Framework\Application;
use PHPUnit\Framework\TestCase;

final class RedisConnectionManagerTest extends TestCase
{
    public function testReusesConnectionForSameConfig(): void
    {
        $factory = new CountingRedisConnectionFactory();
        $manager = new RedisConnectionManager($factory);
        $config = new RedisConfig(host: 'redis', port: 6379, database: 0);

        $first = $manager->connect($config);
        $second = $manager->connect($config);

        self::assertSame($first, $second);
        self::assertSame(1, $factory->connections);
    }

    public function testCreatesDifferentConnectionsForDifferentConfigs(): void
    {
        $factory = new CountingRedisConnectionFactory();
        $manager = new RedisConnectionManager($factory);

        $first = $manager->connect(new RedisConfig(host: 'redis', port: 6379, database: 0));
        $second = $manager->connect(new RedisConfig(host: 'redis', port: 6379, database: 1));

        self::assertNotSame($first, $second);
        self::assertSame(2, $factory->connections);
    }

    public function testExtensionDoesNotOverrideExistingFactory(): void
    {
        $application = Application::create();
        $factory = new CountingRedisConnectionFactory();
        $application->container()->instance(RedisConnectionFactory::class, $factory);

        $application->extend(new RedisExtension());

        self::assertSame($factory, $application->container()->get(RedisConnectionFactory::class));
    }

    public function testConfigNormalizesArrayValues(): void
    {
        $config = RedisConfig::fromArray([
            'host' => 'redis',
            'port' => '6380',
            'timeout' => '1.5',
            'password' => '',
            'database' => '2',
        ]);

        self::assertSame('redis', $config->host);
        self::assertSame(6380, $config->port);
        self::assertSame(1.5, $config->timeout);
        self::assertNull($config->password);
        self::assertSame(2, $config->database);
    }
}

final class CountingRedisConnectionFactory implements RedisConnectionFactory
{
    public int $connections = 0;

    public function connect(RedisConfig $config): RedisClient
    {
        $this->connections++;

        return new FakeRedisClient();
    }
}

final class FakeRedisClient implements RedisClient
{
    /** @var array<string, string> */
    private array $values = [];

    /** @var array<string, list<string>> */
    private array $lists = [];

    public function get(string $key): string|false
    {
        return $this->values[$key] ?? false;
    }

    public function set(string $key, string $value): bool
    {
        $this->values[$key] = $value;

        return true;
    }

    public function setex(string $key, int $ttlSeconds, string $value): bool
    {
        return $this->set($key, $value);
    }

    public function del(string ...$keys): int|false
    {
        $removed = 0;
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->values)) {
                unset($this->values[$key]);
                $removed++;
            }
        }

        return $removed;
    }

    public function rPush(string $key, string $value): int|false
    {
        $this->lists[$key][] = $value;

        return count($this->lists[$key]);
    }

    public function lPop(string $key): string|false
    {
        if (($this->lists[$key] ?? []) === []) {
            return false;
        }

        return array_shift($this->lists[$key]);
    }
}
