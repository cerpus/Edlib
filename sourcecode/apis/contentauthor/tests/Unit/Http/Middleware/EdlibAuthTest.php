<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\EdlibAuth;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class EdlibAuthTest extends TestCase
{
    private Request $request;
    private EdlibAuth $middleware;

    protected function setUp(): void
    {
        $this->middleware = new EdlibAuth();
        $this->request = new Request();
        $this->request->setLaravelSession(
            $session = new Store('test', new ArraySessionHandler(123))
        );
        $session->put('roles', ['admin']);
    }

    public function testGrantsAccessToUserWithValidRole(): void
    {
        $this->assertTrue(
            $this->middleware->handle($this->request, fn() => true, 'admin'),
        );
    }

    public function testDeniesAccessToUserWithoutValidRole(): void
    {
        $this->expectException(UnauthorizedHttpException::class);

        $this->middleware->handle($this->request, fn() => true, 'superadmin');
    }
}
