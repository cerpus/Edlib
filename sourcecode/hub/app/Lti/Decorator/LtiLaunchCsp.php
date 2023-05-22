<?php

declare(strict_types=1);

namespace App\Lti\Decorator;

use App\Http\Middleware\ContentSecurityPolicy;
use App\Lti\LtiLaunch;
use App\Lti\LtiLaunchBuilder;
use App\Lti\Oauth1\Oauth1Credentials;
use App\Lti\Oauth1\Oauth1SignerInterface;
use Illuminate\Http\Request;

/**
 * Exempt LTI tools from CSP on demand
 */
class LtiLaunchCsp extends LtiLaunchBuilder
{
    public function __construct(
        Oauth1SignerInterface $oauth1Signer,
        private readonly Request $request,
    ) {
        // TODO: this class should implement an interface so we aren't bound by
        // a class hierarchy when applying multiple decorators.
        parent::__construct($oauth1Signer);
    }

    public function toPresentationLaunch(
        Oauth1Credentials $credentials,
        string $url,
        string $resourceLinkId,
    ): LtiLaunch {
        ContentSecurityPolicy::allowFrame($this->request, $url);

        return parent::toPresentationLaunch($credentials, $url, $resourceLinkId);
    }

    public function toItemSelectionLaunch(
        Oauth1Credentials $credentials,
        string $url,
        string $itemReturnUrl,
    ): LtiLaunch {
        ContentSecurityPolicy::allowFrame($this->request, $url);
        ContentSecurityPolicy::allowFrame($this->request, $itemReturnUrl);

        return parent::toItemSelectionLaunch($credentials, $url, $itemReturnUrl);
    }
}
