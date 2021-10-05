<?php

namespace App\Libraries\H5P\Interfaces;


interface PackageInterface
{
    public function getElements(): array;

    public function validate(): bool;

    public function getAnswers($index = null);

    public function isComposedComponent(): bool;

    public function setAnswers($answers);

    public function canExtractAnswers(): bool;

    public function getLibraryWithVersion(): string;

    public function alterSource($sourceFile, array $newSource);

    public function getPackageStructure();

    public function getSources();

    public function getPackageAnswers($data);
}