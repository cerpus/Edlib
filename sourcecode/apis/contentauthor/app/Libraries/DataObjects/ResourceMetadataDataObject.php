<?php

namespace App\Libraries\DataObjects;

/**
 * @todo Document what this class represents
 */
class ResourceMetadataDataObject
{
    /**
     * @param string|null $reason One of VersionData constants
     * @param int|string|null $owner Equivalent to authId
     */
    public function __construct(
        public readonly mixed $license,
        public readonly string|null $share = null,
        public readonly string|null $reason = null,
        public readonly int|string|null $owner = null,
        public readonly array $tags = [],
    ) {
    }
}
