<?php

declare(strict_types=1);

namespace Tests\Unit\Lti\ContentItem\Serializer;

use App\Lti\ContentItem\ContentItemPlacement;
use App\Lti\ContentItem\ContentItems;
use App\Lti\ContentItem\Image;
use App\Lti\ContentItem\LtiLinkItem;
use App\Lti\ContentItem\Mapper\ContentItemsMapper;
use App\Lti\ContentItem\PresentationDocumentTarget;
use App\Lti\ContentItem\Serializer\ContentItemsSerializer;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class ContentItemsSerializerTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function testSerializesContentItems(): array
    {
        $contentItems = new ContentItems([
            new LtiLinkItem(
                mediaType: 'application/vnd.ims.lti.v1.ltilink',
                contentItemPlacement: new ContentItemPlacement(
                    displayWidth: 640,
                    displayHeight: 480,
                    presentationDocumentTarget: PresentationDocumentTarget::Iframe,
                    windowTarget: '_top',
                ),
                icon: new Image('http://example.com/icon.jpg', 320, 240),
                thumbnail: new Image('http://example.com/thumbnail.jpg', 32, 24),
                title: 'My Cool Content',
                text: 'A cool text description of my cool content',
                url: 'https://example.com/lti',
            ),
            new LtiLinkItem(
                mediaType: 'application/vnd.ims.lti.v1.ltilink',
                title: 'My Other Cool Content',
            )
        ]);

        $serialized = (new ContentItemsSerializer())->serialize($contentItems);

        $this->assertEquals([
            '@context' => 'http://purl.imsglobal.org/ctx/lti/v1/ContentItem',
            '@graph' => [
                [
                    '@type' => 'LtiLinkItem',
                    'http://purl.imsglobal.org/vocab/lti/v1/ci#icon' => [
                        '@id' => 'http://example.com/icon.jpg',
                        'http://purl.imsglobal.org/vocab/lti/v1/ci#width' => 320,
                        'http://purl.imsglobal.org/vocab/lti/v1/ci#height' => 240,
                    ],
                    'http://purl.imsglobal.org/vocab/lti/v1/ci#thumbnail' => [
                        '@id' => 'http://example.com/thumbnail.jpg',
                        'http://purl.imsglobal.org/vocab/lti/v1/ci#width' => 32,
                        'http://purl.imsglobal.org/vocab/lti/v1/ci#height' => 24,
                    ],
                    'http://purl.imsglobal.org/vocab/lti/v1/ci#mediaType' => 'application/vnd.ims.lti.v1.ltilink',
                    'http://purl.imsglobal.org/vocab/lti/v1/ci#placementAdvice' => [
                        'http://purl.imsglobal.org/vocab/lti/v1/ci#displayWidth' => 640,
                        'http://purl.imsglobal.org/vocab/lti/v1/ci#displayHeight' => 480,
                        'http://purl.imsglobal.org/vocab/lti/v1/ci#presentationDocumentTarget' => 'iframe',
                        'http://purl.imsglobal.org/vocab/lti/v1/ci#windowTarget' => '_top',
                    ],
                    'http://purl.imsglobal.org/vocab/lti/v1/ci#title' => 'My Cool Content',
                    'http://purl.imsglobal.org/vocab/lti/v1/ci#text' => 'A cool text description of my cool content',
                    'http://purl.imsglobal.org/vocab/lti/v1/ci#url' => 'https://example.com/lti',
                ],
                [
                    '@type' => 'LtiLinkItem',
                    'http://purl.imsglobal.org/vocab/lti/v1/ci#mediaType' => 'application/vnd.ims.lti.v1.ltilink',
                    'http://purl.imsglobal.org/vocab/lti/v1/ci#title' => 'My Other Cool Content',
                ],
            ],
        ], $serialized);

        return $serialized;
    }

    #[Depends('testSerializesContentItems')]
    public function testWeCanMapOurOwnSerializedData(array $serialized): void
    {
        $items = (new ContentItemsMapper())->map(json_encode($serialized));

        $this->assertCount(2, $items);
    }
}
