<?php

namespace Tests\Unit;

use App\Exceptions\NotFoundException;
use App\Util;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    /**
     * @test
     */
    public function testHandleEdlibNodeApiRequest(): void
    {
        $jsonData = ['a' => ['b' => 'c']];
        $response = new Response(200, [], json_encode($jsonData, JSON_THROW_ON_ERROR));

        $this->assertSame($jsonData, Util::handleEdlibNodeApiRequest(fn() => $response));
    }

    /**
     * @test
     */
    public function testHandleEdlibNodeApiRequest_withBadJson(): void
    {
        $response = new Response(200, [], '{"a":');

        $this->expectException(JsonException::class);

        Util::handleEdlibNodeApiRequest(fn() => $response);
    }

    /**
     * @test
     */
    public function handleEdlibNodeApiRequest_with404(): void
    {
        $errorMessage = 'oops!';
        $errorData = ['error' => ['parameter' => $errorMessage]];
        $request = new Request('GET', '/');
        $response = new Response(404, [], json_encode($errorData, JSON_THROW_ON_ERROR));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage($errorMessage);

        Util::handleEdlibNodeApiRequest(function () use ($request, $response) {
            throw new RequestException('', $request, $response);
        });
    }

    /**
     * @test
     */
    public function handleEdlibNodeApiRequest_with404AndBadJson(): void
    {
        $request = new Request('GET', '/');
        $response = new Response(404, [], '{"a":');
        $exception = new RequestException('', $request, $response);

        $this->expectExceptionObject($exception);

        Util::handleEdlibNodeApiRequest(function () use ($exception) {
            throw $exception;
        });
    }

    /**
     * @test
     */
    public function handleEdlibNodeApiRequest_withOtherErrorCode(): void
    {
        $request = new Request('GET', '/');
        $response = new Response(500, [], '');
        $exception = new RequestException('Server error', $request, $response);

        $this->expectExceptionObject($exception);

        Util::handleEdlibNodeApiRequest(function () use ($exception) {
            throw $exception;
        });
    }

    /**
     * @test
     */
    public function handleEdlibNodeApiRequest_withNoResponseError(): void
    {
        $request = new Request('GET', '/');
        $exception = new RequestException('Server error', $request);

        $this->expectExceptionObject($exception);

        Util::handleEdlibNodeApiRequest(function () use ($exception) {
            throw $exception;
        });
    }
}
