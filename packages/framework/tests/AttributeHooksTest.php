<?php

declare(strict_types=1);

namespace Bifrost\Framework\Tests;

use Bifrost\Framework\Application;
use Bifrost\Framework\Attributes\Cache;
use Bifrost\Framework\Attributes\Transaction;
use Bifrost\Framework\Contracts\CacheStore;
use Bifrost\Framework\Contracts\TransactionManager;
use Bifrost\Framework\Http\HttpStatusCode;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use PHPUnit\Framework\TestCase;

final class AttributeHooksTest extends TestCase
{
    public function testCacheAttributeReusesStoredResponse(): void
    {
        $application = Application::create();
        $cache = new InMemoryCacheStore();
        $application->container()->instance(CacheStore::class, $cache);
        $application->get('/cached', [CacheControllerStub::class, 'show']);

        $first = $application->handle(new Request(method: 'GET', path: '/cached'));
        $second = $application->handle(new Request(method: 'GET', path: '/cached'));

        self::assertSame('{"count":1}', $first->body());
        self::assertSame('{"count":1}', $second->body());
        self::assertCount(1, $cache->values);
    }

    public function testTransactionAttributeCommitsSuccessfulResponse(): void
    {
        $application = Application::create();
        $transactionManager = new RecordingTransactionManager();
        $application->container()->instance(TransactionManager::class, $transactionManager);
        $application->post('/transaction', [TransactionControllerStub::class, 'store']);

        $response = $application->handle(new Request(method: 'POST', path: '/transaction'));

        self::assertSame(201, $response->status());
        self::assertSame(['begin', 'commit'], $transactionManager->events);
    }

    public function testTransactionAttributeRollsBackErrorResponse(): void
    {
        $application = Application::create();
        $transactionManager = new RecordingTransactionManager();
        $application->container()->instance(TransactionManager::class, $transactionManager);
        $application->post('/transaction/fail', [TransactionControllerStub::class, 'fail']);

        $response = $application->handle(new Request(method: 'POST', path: '/transaction/fail'));

        self::assertSame(400, $response->status());
        self::assertSame(['begin', 'rollback'], $transactionManager->events);
    }

    public function testHttpStatusCodeClassifiesStatusFamilies(): void
    {
        self::assertSame('Created', HttpStatusCode::CREATED->message());
        self::assertTrue(HttpStatusCode::CREATED->isSuccess());
        self::assertTrue(HttpStatusCode::FOUND->isRedirection());
        self::assertTrue(HttpStatusCode::BAD_REQUEST->isClientError());
        self::assertTrue(HttpStatusCode::INTERNAL_SERVER_ERROR->isServerError());
    }
}

final class CacheControllerStub
{
    private static int $count = 0;

    #[Cache(seconds: 60)]
    public function show(Request $request): Response
    {
        self::$count++;

        return Response::json(['count' => self::$count]);
    }
}

final class TransactionControllerStub
{
    #[Transaction]
    public function store(Request $request): Response
    {
        return Response::created(['created' => true]);
    }

    #[Transaction]
    public function fail(Request $request): Response
    {
        return Response::badRequest('Invalid payload');
    }
}

final class InMemoryCacheStore implements CacheStore
{
    /** @var array<string, mixed> */
    public array $values = [];

    public function get(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    public function set(string $key, mixed $value, ?int $ttlSeconds = null): void
    {
        $this->values[$key] = $value;
    }

    public function delete(string $key): void
    {
        unset($this->values[$key]);
    }
}

final class RecordingTransactionManager implements TransactionManager
{
    /** @var list<string> */
    public array $events = [];

    public function begin(): bool
    {
        $this->events[] = 'begin';

        return true;
    }

    public function commit(): bool
    {
        $this->events[] = 'commit';

        return true;
    }

    public function rollback(): bool
    {
        $this->events[] = 'rollback';

        return true;
    }
}
