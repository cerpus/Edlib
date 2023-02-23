<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries\H5P\TranslationServices;

use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\TranslationServices\NynorobotAdapter;
use ArrayObject;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class NynorobotAdapterTest extends TestCase
{
    private MockHandler $mockedResponses;
    private ArrayObject $history;
    private Client $client;

    protected function setUp(): void
    {
        $this->history = new ArrayObject();
        $this->mockedResponses = new MockHandler();

        $handler = HandlerStack::create($this->mockedResponses);
        $handler->push(Middleware::history($this->history));

        $this->client = new Client(['handler' => $handler]);
    }

    public function testCanTranslateTheStuff(): void
    {
        $this->mockedResponses->append(
            new Response(200, ['Content-Type' => 'text/html'], <<<EOHTML
            <html><body>
            <div edlib-translation-path="foo"><p>Några saker</p></div>
            <div edlib-translation-path="0">att översätta</div>
            <div edlib-translation-path="bar"><h1>och testa</h1></div>
            </body></html>
            EOHTML),
        );

        $data = new H5PTranslationDataObject([
            'foo' => '<p>Noen greier</p>',
            'å oversette',
            'bar' => 'og teste',
        ]);

        $adapter = new NynorobotAdapter(
            $this->client,
            NynorobotAdapter::STYLE_RADICAL,
        );
        $translated = $adapter->getTranslations($data);

        $this->assertNotSame($data, $translated);
        $this->assertSame([
            'foo' => "<p>Några saker</p>",
            'att översätta',
            'bar' => '<h1>och testa</h1>',
        ], $translated->getFields());
    }

    public function testThrowsOnHttpFailure(): void
    {
        $this->mockedResponses->append(new Response(500, [], ''));
        $data = new H5PTranslationDataObject(['foo' => 'bar']);
        $adapter = new NynorobotAdapter($this->client, NynorobotAdapter::STYLE_MODERATE);

        $this->expectExceptionMessage('Error from translation service');

        $adapter->getTranslations($data);
    }
}
