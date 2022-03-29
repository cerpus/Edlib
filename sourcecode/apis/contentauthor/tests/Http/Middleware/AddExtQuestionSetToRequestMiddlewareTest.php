<?php

declare(strict_types=1);

namespace Tests\Http\Middleware;

use App\Http\Middleware\AddExtQuestionSetToRequestMiddleware;
use App\SessionKeys;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use PHPUnit\Framework\TestCase;

final class AddExtQuestionSetToRequestMiddlewareTest extends TestCase
{
    private Request $request;

    protected function setUp(): void
    {
        $this->request = new Request();
        $this->request->setLaravelSession(
            new Store('test', new ArraySessionHandler(123)),
        );
    }

    public function testAddsData(): void
    {
        $middleware = new AddExtQuestionSetToRequestMiddleware('local', true);
        $middleware->handle($this->request, fn() => null);

        $this->assertJsonStringEqualsJsonString(
            json_encode(AddExtQuestionSetToRequestMiddleware::QUESTION_SET_DATA),
            $this->request->getSession()->get(SessionKeys::EXT_QUESTION_SET),
        );
    }

    public function testCanBeDisabled(): void
    {
        $middleware = new AddExtQuestionSetToRequestMiddleware('local', false);
        $middleware->handle($this->request, fn() => null);

        $this->assertFalse(
            $this->request->getSession()->has(SessionKeys::EXT_QUESTION_SET),
        );
    }

    public function testNeverEnabledInProduction(): void
    {
        $middleware = new AddExtQuestionSetToRequestMiddleware('production', true);
        $middleware->handle($this->request, fn() => null);

        $this->assertFalse(
            $this->request->getSession()->has(SessionKeys::EXT_QUESTION_SET),
        );
    }
}
