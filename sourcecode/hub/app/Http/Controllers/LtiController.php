<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\SessionScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function to_route;

final readonly class LtiController
{
    public function select(Request $request, SessionScope $scope): RedirectResponse
    {
        $ltiData = $request->attributes->get('lti');
        assert($ltiData !== null);

        $session = $scope->start($request);
        $session->put('lti', $ltiData);

        return to_route('content.index');
    }
}
