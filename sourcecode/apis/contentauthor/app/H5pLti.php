<?php

namespace App;

use App\Http\Requests\LTIRequest;
use Illuminate\Support\Facades\Request;
use InvalidArgumentException;

class H5pLti
{
    public function __construct(
        private readonly string $consumerKey,
        private readonly string $consumerSecret,
    ) {
        if ($this->consumerKey === '') {
            throw new InvalidArgumentException('$consumerKey cannot be an empty string');
        }

        if ($this->consumerSecret === '') {
            throw new InvalidArgumentException('$consumerSecret cannot be an empty string');
        }
    }

    /**
     * @throws Oauth10\MissingSignatureException
     * @throws Oauth10\UnsupportedSignatureException
     */
    public function getValidatedLtiRequest(): LTIRequest|null
    {
        // TODO: take request from parameter
        /** @var \Illuminate\Http\Request $request */
        $request = Request::instance();
        $ltiRequest = LTIRequest::fromRequest($request);
        if ($ltiRequest?->validateOauth10($this->consumerKey, $this->consumerSecret)) {
            return $ltiRequest;
        }
        return null;
    }
}
