<?php

declare(strict_types=1);

namespace App\EdlibResource;

use Cerpus\EdlibResourceKit\Oauth1\Claim;
use Cerpus\EdlibResourceKit\Oauth1\Request;
use Cerpus\EdlibResourceKit\Oauth1\ValidatorInterface;

use function request;

/**
 * Remember that an OAuth 1 request was validated earlier in the request, so the
 * replay attack prevention doesn't kick in.
 */
final readonly class CachedOauth1Validator implements ValidatorInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function validate(Request $request): void
    {
        $signature = $request->has(Claim::SIGNATURE)
            ? $request->get(Claim::SIGNATURE)
            : null;

        if ($signature !== null && request()->attributes->has($signature)) {
            return;
        }

        $this->validator->validate($request);

        request()->attributes->set($request->get(Claim::SIGNATURE), true);
    }
}
