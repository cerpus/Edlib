<?php

declare(strict_types=1);

namespace App\Oembed;

use DOMDocument;
use RuntimeException;

final readonly class Serializer
{
    public function serialize(RichContentResponse $response, OembedFormat $format): string
    {
        return match ($format) {
            OembedFormat::Json => $this->serializeJson($response),
            OembedFormat::Xml => $this->serializeXml($response),
        };
    }

    private function serializeJson(RichContentResponse $response): string
    {
        return json_encode($response->getData(), JSON_THROW_ON_ERROR);
    }

    private function serializeXml(RichContentResponse $response): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElement('oembed');
        foreach ($response->getData() as $key => $value) {
            $root->appendChild($dom->createElement($key, (string) $value));
        }
        $dom->appendChild($root);

        return $dom->saveXML()
            ?: throw new RuntimeException('Serialization failed');
    }
}
