<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\RequestId;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class RequestIdTest extends TestCase
{
    private LoggerInterface&MockObject $psrLogger;
    private Logger $logger;
    private RequestId $middleware;

    protected function setUp(): void
    {
        $this->psrLogger = $this->createMock(LoggerInterface::class);
        $this->logger = new Logger($this->psrLogger);
        $this->middleware = new RequestId($this->logger);
    }

    public function testAddsRequestHeaders(): void
    {
        $request = new Request();
        $this->middleware->handle($request, fn() => true);

        $this->assertIsString($request->header('X-Request-Id'));
        $this->assertTrue(Uuid::isValid($request->header('X-Request-Id')));
    }

    public function testAddsResponseHeaders(): void
    {
        $response = new Response();
        $this->middleware->handle(new Request(), fn() => $response);

        $this->assertIsString($response->headers->get('X-Request-Id'));
        $this->assertTrue(Uuid::isValid($response->headers->get('X-Request-Id')));
    }

    public function testAddsLogContext(): void
    {
        $this->middleware->handle(new Request(), function () {
            $this->psrLogger
                ->expects($this->once())
                ->method('debug')
                ->with(
                    $this->anything(),
                    $this->arrayHasKey('requestId'),
                );

            $this->logger->debug('This is a log message');
        });
    }
}
