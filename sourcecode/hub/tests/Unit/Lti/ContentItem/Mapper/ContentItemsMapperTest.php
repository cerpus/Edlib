<?php

declare(strict_types=1);

namespace Tests\Unit\Lti\ContentItem\Mapper;

use App\Lti\ContentItem\ContentItemPlacement;
use App\Lti\ContentItem\Image;
use App\Lti\ContentItem\Mapper\ContentItemsMapper;
use App\Lti\ContentItem\PresentationDocumentTarget;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

final class ContentItemsMapperTest extends TestCase
{
    private ContentItemsMapper $mapper;

    #[Before]
    protected function setUpMapper(): void
    {
        $this->mapper = new ContentItemsMapper();
    }

    public function testItMapsAllTheStuff(): void
    {
        $contentItems = $this->mapper->map(json_encode([
            '@context' => 'http://purl.imsglobal.org/ctx/lti/v1/ContentItem',
            '@graph' => [
                [
                    'mediaType' => 'application/vnd.ims.lti.v1.ltilink',
                    'title' => 'My cool content',
                    'placementAdvice' => [
                        'displayWidth' => 640,
                        'displayHeight' => 480,
                        'presentationDocumentTarget' => 'iframe',
                        'windowTarget' => '_top',
                    ],
                    'thumbnail' => [
                        '@id' => 'http://example.com/thumb',
                        'width' => 32,
                        'height' => 24,
                    ],
                    'icon' => [
                        '@id' => 'http://example.com/icon',
                        'width' => 320,
                        'height' => 240,
                    ],
                    'url' => 'http://example.com/lti',
                ],
            ],
        ]));

        $this->assertArrayHasKey(0, $contentItems);
        $this->assertSame('application/vnd.ims.lti.v1.ltilink', $contentItems[0]->getMediaType());
        $this->assertSame('My cool content', $contentItems[0]->getTitle());
        $this->assertSame('http://example.com/lti', $contentItems[0]->getUrl());
        $this->assertEquals(
            new Image('http://example.com/icon', 320, 240),
            $contentItems[0]->getIcon(),
        );
        $this->assertEquals(
            new Image('http://example.com/thumb', 32, 24),
            $contentItems[0]->getThumbnail(),
        );
        $this->assertEquals(
            new ContentItemPlacement(640, 480, PresentationDocumentTarget::Iframe, '_top'),
            $contentItems[0]->getContentItemPlacement(),
        );
    }
}
