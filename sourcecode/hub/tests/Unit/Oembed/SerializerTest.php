<?php

declare(strict_types=1);

namespace Tests\Unit\Oembed;

use App\Oembed\OembedFormat;
use App\Oembed\RichContentResponse;
use App\Oembed\Serializer;
use PHPUnit\Framework\TestCase;

use function json_decode;

use const JSON_THROW_ON_ERROR;

final class SerializerTest extends TestCase
{
    public function testSerializesResponseAsJson(): void
    {
        $response = new RichContentResponse(
            '<iframe src="http://example.com"></iframe>',
            640,
            480,
            'The content title',
        );

        $json = (new Serializer())->serialize($response, OembedFormat::Json);

        $this->assertEqualsCanonicalizing([
            'version' => '1.0',
            'type' => 'rich',
            'title' => 'The content title',
            'html' => '<iframe src="http://example.com"></iframe>',
            'width' => 640,
            'height' => 480,
        ], json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR));
    }

    public function testSerializesResponseAsXml(): void
    {
        $content = new RichContentResponse(
            '<iframe src="http://example.com"></iframe>',
            640,
            480,
            'The content title',
        );

        $xml = (new Serializer())->serialize($content, OembedFormat::Xml);

        $this->assertXmlStringEqualsXmlString(<<<EOXML
        <?xml version="1.0" encoding="utf-8"?>
        <oembed>
            <version>1.0</version>
            <type>rich</type>
            <title>The content title</title>
            <html>&lt;iframe src="http://example.com"&gt;&lt;/iframe&gt;</html>
            <width>640</width>
            <height>480</height>
        </oembed>
        EOXML, $xml);
    }
}
