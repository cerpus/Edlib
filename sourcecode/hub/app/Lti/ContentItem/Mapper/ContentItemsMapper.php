<?php

declare(strict_types=1);

namespace App\Lti\ContentItem\Mapper;

use App\Lti\ContentItem\ContentItemPlacement;
use App\Lti\ContentItem\ContentItems;
use App\Lti\ContentItem\Image;
use App\Lti\ContentItem\JsonldDocumentLoader;
use App\Lti\ContentItem\LtiLinkItem;
use App\Lti\ContentItem\PresentationDocumentTarget;
use ML\JsonLD\DocumentLoaderInterface;
use ML\JsonLD\JsonLD;
use stdClass;

use function assert;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final readonly class ContentItemsMapper
{
    public function __construct(
        private DocumentLoaderInterface $documentLoader = new JsonldDocumentLoader(),
    ) {
    }

    public function map(string|stdClass $dataOrJson): ContentItems
    {
        if (is_string($dataOrJson)) {
            $data = json_decode($dataOrJson, flags: JSON_THROW_ON_ERROR);
            assert($data instanceof stdClass);
        } else {
            $data = $dataOrJson;
        }

        $items = JsonLD::expand($data, options: [
            'documentLoader' => $this->documentLoader,
        ]);

        return new ContentItems(array_map(fn (stdClass $item) => new LtiLinkItem(
            $item->{ContentItems::PROP_MEDIA_TYPE}[0]->{'@value'}
                ?? throw new \Exception('missing media type'),
            $this->mapPlacementAdvice($item),
            $this->mapImage($item, ContentItems::PROP_ICON),
            $this->mapImage($item, ContentItems::PROP_THUMBNAIL),
            $item->{ContentItems::PROP_TEXT}[0]->{'@value'} ?? null,
            $item->{ContentItems::PROP_TITLE}[0]->{'@value'} ?? null,
            $item->{ContentItems::PROP_URL}[0]->{'@value'} ?? null,
        ), $items));
    }

    private function mapPlacementAdvice(stdClass $data): ContentItemPlacement|null
    {
        $advice = $data->{ContentItems::PROP_PLACEMENT_ADVICE}[0] ?? null;

        if ($advice === null) {
            return null;
        }

        return new ContentItemPlacement(
            $advice->{ContentItems::PROP_DISPLAY_WIDTH}[0]->{'@value'} ?? null,
            $advice->{ContentItems::PROP_DISPLAY_HEIGHT}[0]->{'@value'} ?? null,
            // FIXME: not sure why sometimes this is @value and other times @id?
            PresentationDocumentTarget::tryFromShortName(
                $advice->{ContentItems::PROP_PRESENTATION_DOCUMENT_TARGET}[0]->{'@value'} ??
                $advice->{ContentItems::PROP_PRESENTATION_DOCUMENT_TARGET}[0]->{'@id'}
            ) ?? PresentationDocumentTarget::from(
                $advice->{ContentItems::PROP_PRESENTATION_DOCUMENT_TARGET}[0]->{'@value'} ??
                $advice->{ContentItems::PROP_PRESENTATION_DOCUMENT_TARGET}[0]->{'@id'}
            ),
            $advice->{ContentItems::PROP_WINDOW_TARGET}[0]->{'@value'} ?? null,
        );
    }

    private function mapImage(stdClass $data, string $path): Image|null
    {
        $image = $data->{$path}[0] ?? null;

        if (!$image) {
            return null;
        }

        return new Image(
            $image->{'@id'},
            $image->{ContentItems::PROP_WIDTH}[0]->{'@value'} ?? null,
            $image->{ContentItems::PROP_HEIGHT}[0]->{'@value'} ?? null,
        );
    }
}
