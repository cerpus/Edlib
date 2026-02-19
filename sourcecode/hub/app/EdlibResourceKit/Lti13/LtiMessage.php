<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13;

use App\EdlibResourceKit\Lti13\Attribute\Claim;

/**
 * Represents the foundation of any LTI message.
 *
 * @see http://www.imsglobal.org/spec/lti/v1p3/#lti-message-general-details
 */
abstract class LtiMessage
{
    /**
     * @see http://www.imsglobal.org/spec/lti/v1p3/#lti-version-claim
     */
    #[Claim('https://purl.imsglobal.org/spec/lti/claim/version')]
    public const VERSION = '1.3.0';

    /**
     * @see http://www.imsglobal.org/spec/lti/v1p3/#lti-deployment-id-claim
     */
    #[Claim('https://purl.imsglobal.org/spec/lti/claim/deployment_id')]
    private readonly string $deploymentId;

    #[Claim('sub')]
    private readonly string|null $subject;

    #[Claim]
    private readonly string|null $familyName;

    #[Claim]
    private readonly string|null $givenName;

    #[Claim]
    private readonly string|null $name;

    #[Claim]
    private readonly string|null $email;

    #[Claim]
    private readonly string|null $locale;

    public function __construct(
        string $deploymentId,
        string|null $subject = null,
        string|null $familyName = null,
        string|null $givenName = null,
        string|null $name = null,
        string|null $email = null,
        string|null $locale = null,
    ) {
        $this->deploymentId = $deploymentId;
        $this->subject = $subject;
        $this->familyName = $familyName;
        $this->givenName = $givenName;
        $this->name = $name;
        $this->email = $email;
        $this->locale = $locale;
    }

    #[Claim('https://purl.imsglobal.org/spec/lti/claim/message_type')]
    abstract public function getMessageType(): string;

    public function getDeploymentId(): string
    {
        return $this->deploymentId;
    }

    public function getSubject(): string|null
    {
        return $this->subject;
    }

    public function getFamilyName(): string|null
    {
        return $this->familyName;
    }

    public function getGivenName(): string|null
    {
        return $this->givenName;
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function getEmail(): string|null
    {
        return $this->email;
    }

    public function getLocale(): string|null
    {
        return $this->locale;
    }
}
