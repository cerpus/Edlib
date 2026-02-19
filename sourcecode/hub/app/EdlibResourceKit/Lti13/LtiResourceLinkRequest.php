<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13;

use App\EdlibResourceKit\Lti13\Attribute\Claim;

/**
 * @see http://www.imsglobal.org/spec/lti/v1p3/#resource-link-launch-request-message
 */
class LtiResourceLinkRequest extends LtiMessage
{
    /**
     * @see http://www.imsglobal.org/spec/lti/v1p3/#target-link-uri
     */
    #[Claim('https://purl.imsglobal.org/spec/lti/claim/target_link_uri')]
    private readonly string $targetLinkUri;

    /**
     * @see http://www.imsglobal.org/spec/lti/v1p3/#resource-link-claim
     */
    #[Claim('https://purl.imsglobal.org/spec/lti/claim/resource_link')]
    private readonly ResourceLink $resourceLink;

    /**
     * @var string[]
     * @see http://www.imsglobal.org/spec/lti/v1p3/#roles-claim
     */
    #[Claim('https://purl.imsglobal.org/spec/lti/claim/roles')]
    private readonly array $roles;

    /**
     * @param array<string> $roles
     */
    public function __construct(
        string $deploymentId,
        string $targetLinkUri,
        ResourceLink $resourceLink,
        array $roles = [],
        string|null $subject = null,
        string|null $familyName = null,
        string|null $givenName = null,
        string|null $name = null,
        string|null $email = null,
        string|null $locale = null,
    ) {
        parent::__construct($deploymentId, $subject, $familyName, $givenName, $name, $email, $locale);

        $this->targetLinkUri = $targetLinkUri;
        $this->resourceLink = $resourceLink;
        $this->roles = $roles;
    }

    public function getMessageType(): string
    {
        return 'LtiResourceLinkRequest';
    }

    public function getResourceLink(): ResourceLink
    {
        return $this->resourceLink;
    }

    public function getTargetLinkUri(): string
    {
        return $this->targetLinkUri;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
