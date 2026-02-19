<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13;

use App\EdlibResourceKit\Lti13\Attribute\Claim;

/**
 * @see http://www.imsglobal.org/spec/lti/v1p3/#platform-instance-claim
 */
class ToolPlatform
{
    public function __construct(
        #[Claim] private readonly string $guid,
        #[Claim] private readonly string|null $contactEmail = null,
        #[Claim] private readonly string|null $description = null,
        #[Claim] private readonly string|null $name = null,
        #[Claim] private readonly string|null $url = null,
        #[Claim] private readonly string|null $productFamilyCode = null,
        #[Claim] private readonly string|null $version = null,
    ) {
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getContactEmail(): string|null
    {
        return $this->contactEmail;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function getUrl(): string|null
    {
        return $this->url;
    }

    public function getProductFamilyCode(): string|null
    {
        return $this->productFamilyCode;
    }

    public function getVersion(): string|null
    {
        return $this->version;
    }
}
