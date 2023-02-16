<?php

declare(strict_types=1);

namespace App\Libraries\H5P\Dataobjects;

use JsonSerializable;

class H5PTranslationDataObject implements JsonSerializable
{
    /**
     * @param array<string, string> $fields
     */
    public function __construct(
        private readonly array $fields,
        private readonly string|null $id = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'document' => $this->fields,
        ];
    }
}
