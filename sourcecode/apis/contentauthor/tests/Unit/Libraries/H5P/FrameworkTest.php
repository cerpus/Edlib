<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries\H5P;

use App\Libraries\H5P\Framework;
use ArrayObject;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Filesystem\Filesystem;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class FrameworkTest extends TestCase
{
    /** @var ArrayObject<int, array{request: RequestInterface, response: ResponseInterface}> */
    private ArrayObject $history;

    private Framework $framework;

    private MockHandler $mockedResponses;

    protected function setUp(): void
    {
        parent::setUp();

        $this->history = new ArrayObject();
        $this->mockedResponses = new MockHandler();

        $handler = HandlerStack::create($this->mockedResponses);
        $handler->push(Middleware::history($this->history));

        $client = new Client(['handler' => $handler]);

        $this->framework = new Framework(
            $client,
            $this->createMock(PDO::class),
            $this->createMock(Filesystem::class),
        );
    }

    public function testFetchExternalData(): void
    {
        $this->mockedResponses->append(new Response(200, [], 'Some body'));

        $this->assertSame(
            'Some body',
            $this->framework->fetchExternalData('http://www.example.com'),
        );
    }

    public function testFetchExternalDataNonBlocking(): void
    {
        $this->mockedResponses->append(new Response(200, [], 'Some body'));

        $data = $this->framework->fetchExternalData(
            'http://www.example.com',
            blocking: false,
        );

        $this->assertNull($data);
        $this->assertSame(
            'http://www.example.com',
            (string) $this->history[0]['request']->getUri(),
        );
        $this->assertSame(0, $this->history[0]['response']->getBody()->tell());
    }

    public function testFetchExternalDataWithData(): void
    {
        $this->mockedResponses->append(new Response(200, [], 'Some body'));

        $this->framework->fetchExternalData('http://www.example.com', [
            'foo' => 'bar',
        ]);

        $this->assertSame(
            'foo=bar',
            $this->history[0]['request']->getBody()->getContents(),
        );
    }

    public function testFetchExternalDataWithFullData(): void
    {
        $this->mockedResponses->append(new Response(200, [], 'Some body'));

        $response = $this->framework->fetchExternalData(
            'http://www.example.com',
            [
                'foo' => 'bar',
            ],
            fullData: true,
        );

        $this->assertSame(
            'foo=bar',
            $this->history[0]['request']->getBody()->getContents(),
        );
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('headers', $response);
        $this->assertArrayHasKey('data', $response);
        $this->assertSame(200, $response['status']);
        $this->assertSame('Some body', $response['data']);
    }

    public function testFetchExternalDataWithGuzzleError(): void
    {
        $this->mockedResponses->append(new TransferException());

        $this->assertNull(
            $this->framework->fetchExternalData('http://www.example.com'),
        );
    }

    public function testFetchExternalDataWithOtherException(): void
    {
        $e = new Exception('oops');
        $this->mockedResponses->append($e);

        $this->expectExceptionObject($e);

        $this->framework->fetchExternalData('http://www.example.com');
    }

    public function testGetInfoMessages(): void
    {
        $this->assertSame([], $this->framework->getMessages('info'));
    }

    public function testAddInfoMessage(): void
    {
        $this->framework->setInfoMessage('this is some info');
        $this->framework->setInfoMessage('this is more info');
        $this->framework->setErrorMessage('this is not info');

        $this->assertSame([
            'this is some info',
            'this is more info',
        ], $this->framework->getMessages('info'));
    }

    public function testGetErrorMessages(): void
    {
        $this->assertSame([], $this->framework->getMessages('error'));
    }

    public function testAddErrorMessage(): void
    {
        $this->framework->setErrorMessage('this is an error');
        $this->framework->setErrorMessage('this is another error');
        $this->framework->setInfoMessage('this is not an error');

        $this->assertSame([
            'this is an error',
            'this is another error',
        ], $this->framework->getMessages('error'));
    }

    public function testGetMessagesOfUnknownType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->framework->getMessages('unknown');
    }
}
