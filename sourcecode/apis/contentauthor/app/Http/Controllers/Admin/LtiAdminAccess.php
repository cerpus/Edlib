<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Lti\Lti;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function abort;
use function redirect;

final readonly class LtiAdminAccess
{
    public function __construct(private Lti $lti) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $ltiRequest = $this->lti->getRequest($request);

        if (!$ltiRequest?->isAdministrator()) {
            abort(403, 'Missing Administrator role in LTI launch');
        }

        Auth::guard('sso')->login(new GenericUser([
            'name' => $ltiRequest->getUserFullName(),
            'roles' => ['superadmin'],
        ]));

        return redirect()->route('admin');
    }
}
