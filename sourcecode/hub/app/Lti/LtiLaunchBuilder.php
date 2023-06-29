<?php

declare(strict_types=1);

namespace App\Lti;

use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;

class LtiLaunchBuilder
{
    private int|null $width = null;
    private int|null $height = null;

    /**
     * @var string[]
     */
    private array $claims = [
        'lti_version' => 'LTI-1p0',
    ];

    public function __construct(
        private readonly SignerInterface $oauth1Signer,
    ) {
    }

    public function withClaim(string $name, string $value): static
    {
        $self = clone $this;
        $self->claims = [...$self->claims, $name => $value];

        return $self;
    }

    public function withWidth(int $width): static
    {
        $self = $this->withClaim('launch_presentation_width', (string) $width);
        $self->width = $width;

        return $self;
    }

    public function withHeight(int $height): static
    {
        $self = $this->withClaim('launch_presentation_height', (string) $height);
        $self->height = $height;

        return $self;
    }

    public function toPresentationLaunch(
        Credentials $credentials,
        string $url,
        string $resourceLinkId,
    ): LtiLaunch {
        $request = new Oauth1Request('POST', $url, [
            ...$this->claims,
            'lti_message_type' => 'basic-lti-launch-request',
            'resource_link_Id' => $resourceLinkId,
        ]);
        $request = $this->oauth1Signer->sign($request, $credentials);

        return new LtiLaunch($request, $this->width, $this->height);
    }

    public function toItemSelectionLaunch(
        Credentials $credentials,
        string $url,
        string $itemReturnUrl,
    ): LtiLaunch {
        $request = new Oauth1Request('POST', $url, [
            ...$this->claims,
            'content_item_return_url' => $itemReturnUrl,
            'lti_message_type' => 'ContentItemSelectionRequest',
        ]);
        $request = $this->oauth1Signer->sign($request, $credentials);

        return new LtiLaunch($request, $this->width, $this->height);
    }
}
