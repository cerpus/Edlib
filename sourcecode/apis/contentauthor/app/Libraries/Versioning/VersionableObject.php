<?php


namespace App\Libraries\Versioning;


interface VersionableObject
{
    function getId(): string;
    function getOwnerId(): string;
    function setParentVersionId(string $parentVersionId): bool;
    function setVersionId(string $versionId);
    function save();
}
