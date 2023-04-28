<?php

declare(strict_types=1);

namespace App\Lti\ContentItem;

use App\Support\Arrays;
use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;
use function array_is_list;
use function count;

/**
 * @template-implements ArrayAccess<int, LtiLinkItem>
 * @template-implements IteratorAggregate<int, LtiLinkItem>
 */
final class ContentItems implements ArrayAccess, Countable, IteratorAggregate
{
    public const CONTEXT = 'http://purl.imsglobal.org/ctx/lti/v1/ContentItem';

    public const PROP_DISPLAY_HEIGHT = 'http://purl.imsglobal.org/vocab/lti/v1/ci#displayHeight';
    public const PROP_DISPLAY_WIDTH = 'http://purl.imsglobal.org/vocab/lti/v1/ci#displayWidth';
    public const PROP_HEIGHT = 'http://purl.imsglobal.org/vocab/lti/v1/ci#height';
    public const PROP_ICON = 'http://purl.imsglobal.org/vocab/lti/v1/ci#icon';
    public const PROP_MEDIA_TYPE = 'http://purl.imsglobal.org/vocab/lti/v1/ci#mediaType';
    public const PROP_PLACEMENT_ADVICE = 'http://purl.imsglobal.org/vocab/lti/v1/ci#placementAdvice';
    public const PROP_PRESENTATION_DOCUMENT_TARGET = 'http://purl.imsglobal.org/vocab/lti/v1/ci#presentationDocumentTarget';
    public const PROP_TEXT = 'http://purl.imsglobal.org/vocab/lti/v1/ci#text';
    public const PROP_THUMBNAIL = 'http://purl.imsglobal.org/vocab/lti/v1/ci#thumbnail';
    public const PROP_TITLE = 'http://purl.imsglobal.org/vocab/lti/v1/ci#title';
    public const PROP_URL = 'http://purl.imsglobal.org/vocab/lti/v1/ci#url';
    public const PROP_WIDTH = 'http://purl.imsglobal.org/vocab/lti/v1/ci#width';
    public const PROP_WINDOW_TARGET = 'http://purl.imsglobal.org/vocab/lti/v1/ci#windowTarget';

    /**
     * @param list<int, LtiLinkItem> $items
     */
    public function __construct(private array $items)
    {
        assert(array_is_list($items));
        assert(Arrays::allAreOfType($items, LtiLinkItem::class));
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): LtiLinkItem
    {
        return $this->items[$offset] ?? throw new OutOfBoundsException();
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('This collection is readonly');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('This collection is readonly');
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
