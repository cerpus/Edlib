<?php

declare(strict_types=1);

namespace App\Traits;

use App\Libraries\DataObjects\LtiContent;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use function now;
use function serialize;

trait ReturnToCore
{
    public function getRedirectToCoreUrl(
        LtiContent $content,
        string|null $sessionKey = null,
    ): string {
        if ($sessionKey === null) {
            return $content->editUrl ?? $content->url;
        }

        $ltiRequest = Session::get('lti_requests.' . $sessionKey);

        return URL::signedRoute('lti-return', [
            'lti_content' => serialize($content),
            'lti_request' => serialize($ltiRequest),
        ], now()->addHour());
    }
}
