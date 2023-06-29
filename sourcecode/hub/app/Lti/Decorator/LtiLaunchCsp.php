<?php

declare(strict_types=1);

namespace App\Lti\Decorator;

use App\Http\Middleware\ContentSecurityPolicy;
use App\Lti\LtiLaunch;
use App\Lti\LtiLaunchBuilder;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Illuminate\Http\Request;

/**
 * Exempt LTI tools from CSP on demand
 */
class LtiLaunchCsp extends LtiLaunchBuilder
{
    public function __construct(
        SignerInterface $oauth1Signer,
        private readonly Request $request,
    ) {
        // TODO: this class should implement an interface so we aren't bound by
        // a class hierarchy when applying multiple decorators.
        parent::__construct($oauth1Signer);
    }

    public function toPresentationLaunch(
        Credentials $credentials,
        string $url,
        string $resourceLinkId,
    ): LtiLaunch {
        ContentSecurityPolicy::allowFrame($this->request, $url);

        return parent::toPresentationLaunch($credentials, $url, $resourceLinkId);
    }

    public function toItemSelectionLaunch(
        Credentials $credentials,
        string $url,
        string $itemReturnUrl,
    ): LtiLaunch {
        ContentSecurityPolicy::allowFrame($this->request, $url);
        ContentSecurityPolicy::allowFrame($this->request, $itemReturnUrl);

        return parent::toItemSelectionLaunch($credentials, $url, $itemReturnUrl);
    }
}
