<?php

declare(strict_types=1);

namespace App\Lti;

use App\Events\LaunchContent;
use App\Events\LaunchLti;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Illuminate\Contracts\Events\Dispatcher;

use function assert;

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
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function getClaim(string $name): string|null
    {
        return $this->claims[$name] ?? null;
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
        ContentVersion $contentVersion,
        string $url,
    ): LtiLaunch {
        $tool = $contentVersion->tool;
        assert($tool !== null);

        $launch = $this->withClaim('lti_message_type', 'basic-lti-launch-request');

        $event = new LaunchLti($url, $launch, $tool);
        $this->dispatcher->dispatch($event);

        $event = new LaunchContent($url, $contentVersion, $event->getLaunch());
        $this->dispatcher->dispatch($event);

        $request = new Oauth1Request('POST', $url, $event->getLaunch()->claims);
        $request = $this->oauth1Signer->sign($request, $tool->getOauth1Credentials());

        return new LtiLaunch($request, $this->width, $this->height);
    }

    public function toItemSelectionLaunch(
        LtiTool $tool,
        string $url,
        string $itemReturnUrl,
    ): LtiLaunch {
        $launch = $this
            ->withClaim('accept_media_types', 'application/vnd.ims.lti.v1.ltilink')
            ->withClaim('accept_presentation_document_targets', 'iframe')
            ->withClaim('content_item_return_url', $itemReturnUrl)
            ->withClaim('lti_message_type', 'ContentItemSelectionRequest');

        $event = new LaunchLti($url, $launch, $tool);
        $this->dispatcher->dispatch($event);

        $request = new Oauth1Request('POST', $url, $event->getLaunch()->claims);
        $request = $this->oauth1Signer->sign($request, $tool->getOauth1Credentials());

        return new LtiLaunch($request, $launch->width, $launch->height);
    }
}
