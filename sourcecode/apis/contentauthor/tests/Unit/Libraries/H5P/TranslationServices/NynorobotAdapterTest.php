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
use function json_encode;

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
            $this->translatedResponse('Några saker'),
            $this->translatedResponse('att översätta'),
            $this->translatedResponse('och testa'),
        );

        $data = new H5PTranslationDataObject();
        $data->setFieldsFromArray([
            'foo' => 'Noen greier',
            'å oversette',
            'bar' => 'og teste',
        ]);

        $adapter = new NynorobotAdapter(
            $this->client,
            NynorobotAdapter::STYLE_RADICAL,
            2,
        );
        $translated = $adapter->getTranslations($data);

        $this->assertNotSame($data, $translated);
        $this->assertSame([
            'foo' => 'Några saker',
            'att översätta',
            'bar' => 'och testa',
        ], $translated->getDocument());
    }

    public function testThrowsOnHttpFailure(): void
    {
        $this->mockedResponses->append(new Response(500, [], ''));
        $data = new H5PTranslationDataObject();
        $data->setFieldsFromArray(['foo']);
        $adapter = new NynorobotAdapter($this->client, NynorobotAdapter::STYLE_MODERATE);

        $this->expectExceptionMessage('Translation failed');

        $adapter->getTranslations($data);
    }

    private function translatedResponse(string $text): Response
    {
        return new Response(200, [], json_encode([
            'responseData' => ['translatedText' => $text],
            'responseDetails' => null,
            'responseStatus' => 200,
        ]));
    }
}
