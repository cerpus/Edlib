<?php

namespace App\Libraries\Versioning;

interface VersionableObject
{
    public function getId(): string;
    public function getOwnerId(): string;
    public function setParentVersionId(string $parentVersionId): bool;
    public function setVersionId(string $versionId);
    public function save();
}
