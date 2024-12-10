<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreLtiPlatformRequest;
use App\Models\LtiPlatform;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function route;

final class LtiPlatformController
{
    public function index(Request $request): View
    {
        $createdId = $request->session()->get('created-lti-platform');

        $createdPlatform = $createdId
            ? LtiPlatform::where('id', $createdId)->first()
            : null;

        return view('admin.lti-platforms.index', [
            'createdPlatform' => $createdPlatform,
            'platforms' => LtiPlatform::orderByDesc('id')->paginate(),
        ]);
    }

    public function store(StoreLtiPlatformRequest $request): RedirectResponse
    {
        $platform = new LtiPlatform();
        $platform->fill($request->validated());
        $platform->save();

        $request->session()->flash('created-lti-platform', $platform->id);

        return to_route('admin.lti-platforms.index');
    }

    public function destroy(LtiPlatform $platform, Request $request): Response
    {
        $platform->delete();

        $request->session()->flash('alert', trans('messages.alert-lti-platform-removed'));

        if ($request->header('HX-Request')) {
            return response()->noContent()
                ->header('HX-Redirect', route('admin.lti-platforms.index'));
        }

        return to_route('admin.lti-platforms.index');
    }
}
