<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Context;

use App\EdlibResourceKit\Lti\Message\DeepLinking\PresentationDocumentTarget;
use DateTimeImmutable;
use function array_filter;
use function array_map;
use function preg_replace;

final class DeepLinkingProps
{
    public const JSONLD_VOCAB = 'http://purl.imsglobal.org/ctx/lti/v1/ContentItem';

    public const COPY_ADVICE = 'copyAdvice';
    public const EXPIRES_AT = 'expiresAt';
    public const DISPLAY_HEIGHT = 'displayHeight';
    public const DISPLAY_WIDTH = 'displayWidth';
    public const EXTRA_CREDIT_MAXIMUM = 'extraCreditMaximum';
    public const HEIGHT = 'height';
    public const ICON = 'icon';
    public const LINE_ITEM = 'lineItem';
    public const MEDIA_TYPE = 'mediaType';
    public const NORMAL_MAXIMUM = 'normalMaximum';
    public const PLACEMENT_ADVICE = 'placementAdvice';
    public const PRESENTATION_DOCUMENT_TARGET = 'presentationDocumentTarget';
    public const SCORE_CONSTRAINTS = 'scoreConstraints';
    public const TEXT = 'text';
    public const THUMBNAIL = 'thumbnail';
    public const TITLE = 'title';
    public const TOTAL_MAXIMUM = 'totalMaximum';
    public const URL = 'url';
    public const WIDTH = 'width';
    public const WINDOW_TARGET = 'windowTarget';

    public const TYPE_ANY_URI = 'http://www.w3.org/2001/XMLSchema#anyURI';
    public const TYPE_BOOLEAN = 'http://www.w3.org/2001/XMLSchema#boolean';
    public const TYPE_DATETIME = 'http://www.w3.org/2001/XMLSchema#date';
    public const TYPE_FLOAT = 'http://www.w3.org/2001/XMLSchema#float';
    public const TYPE_INTEGER = 'http://www.w3.org/2001/XMLSchema#integer';
    public const TYPE_NORMALIZED_STRING = 'http://www.w3.org/2001/XMLSchema#normalizedString';

    public static function getCopyAdvice(array $data): bool|null
    {
        return self::getOfType($data, self::COPY_ADVICE, self::TYPE_BOOLEAN);
    }

    public static function getDisplayHeight(array $data): int|null
    {
        return self::getOfType($data, self::DISPLAY_HEIGHT, self::TYPE_INTEGER);
    }

    public static function getDisplayWidth(array $data): int|null
    {
        return self::getOfType($data, self::DISPLAY_WIDTH, self::TYPE_INTEGER);
    }

    public static function getExpiresAt(array $data): DateTimeImmutable|null
    {
        return self::getOfType($data, self::EXPIRES_AT, self::TYPE_DATETIME);
    }

    public static function getExtraCreditMaximum(array $data): float|null
    {
        return self::getOfType($data, self::EXTRA_CREDIT_MAXIMUM, self::TYPE_FLOAT);
    }

    public static function getHeight(array $data): int|null
    {
        return self::getOfType($data, self::HEIGHT, self::TYPE_INTEGER);
    }

    public static function getMediaType(array $data): string|null
    {
        return self::getOfType($data, self::MEDIA_TYPE, self::TYPE_NORMALIZED_STRING);
    }

    public static function getNormalMaximum(array $data): float|null
    {
        return self::getOfType($data, self::NORMAL_MAXIMUM, self::TYPE_FLOAT);
    }

    public static function getPresentationDocumentTarget(array $data): PresentationDocumentTarget|null
    {
        if (!isset($data[self::PRESENTATION_DOCUMENT_TARGET])) {
            return null;
        }

        return PresentationDocumentTarget::tryFrom($data[self::PRESENTATION_DOCUMENT_TARGET]);
    }

    public static function getText(array $data): string|null
    {
        return self::getOfType($data, self::TEXT, self::TYPE_NORMALIZED_STRING);
    }

    public static function getTitle(array $data): string|null
    {
        return self::getOfType($data, self::TITLE, self::TYPE_NORMALIZED_STRING);
    }

    public static function getUrl(array $data): string|null
    {
        return self::getOfType($data, self::URL, self::TYPE_ANY_URI);
    }

    public static function getWidth(array $data): int|null
    {
        return self::getOfType($data, self::WIDTH, self::TYPE_INTEGER);
    }

    public static function getWindowTarget(array $data): string|null
    {
        return self::getOfType($data, self::WINDOW_TARGET, self::TYPE_NORMALIZED_STRING);
    }

    public static function getOfType(array $data, string $prop, string $type): mixed
    {
        $value = $data[$prop] ?? null;

        return self::coerce($value, $type);
    }

    public static function getArrayOfType(array $data, string $prop, string $type): array
    {
        if (!isset($data[$prop])) {
            return [];
        }

        if (is_array($data[$prop])) {
            $array = array_map(
                fn ($value) => self::coerce($value, $type),
                $data[$prop],
            );
        } else {
            $array = [self::getOfType($data, $prop, $type)];
        }

        return array_filter($array, fn ($item) => $item !== null);
    }

    private static function coerce(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            self::TYPE_BOOLEAN => (bool) $value,
            self::TYPE_DATETIME => new DateTimeImmutable($value),
            self::TYPE_FLOAT => (float) $value,
            self::TYPE_INTEGER => (int) $value,
            self::TYPE_NORMALIZED_STRING => preg_replace('/[\r\n\t]+/', '', $value),
            default => $value,
        };
    }

    private function __construct()
    {
    }
}
