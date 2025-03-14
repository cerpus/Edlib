<?php

namespace App\Libraries\DataObjects;

/**
 * @todo Document what this class represents
 */
readonly class ResourceMetadataDataObject
{
    /**
     * @param string|null $reason One of VersionData constants
     */
    public function __construct(
        public mixed $license,
        public string|null $reason = null,
        public array $tags = [],
    ) {}
}
