<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ContentRole;
use App\Http\Requests\AddContextToLtiPlatformRequest;
use App\Http\Requests\StoreLtiPlatformRequest;
use App\Http\Requests\UpdateLtiPlatformRequest;
use App\Models\Context;
use App\Models\LtiPlatform;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function redirect;
use function route;
use function to_route;

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

    public function edit(LtiPlatform $platform): View
    {
        return view('admin.lti-platforms.edit', [
            'platform' => $platform,
        ]);
    }

    public function update(LtiPlatform $platform, UpdateLtiPlatformRequest $request): RedirectResponse
    {
        $platform->fill($request->validated());
        $platform->save();

        return to_route('admin.lti-platforms.index')
            ->with('alert', trans('messages.alert-lti-platform-updated'));
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

    public function contexts(LtiPlatform $platform): View
    {
        // @phpstan-ignore larastan.noUnnecessaryCollectionCall
        $availableContexts = Context::all()
            ->diff($platform->contexts)
            ->mapWithKeys(fn(Context $context) => [$context->id => $context->name]);

        return view('admin.lti-platforms.contexts', [
            'available_contexts' => $availableContexts,
            'platform' => $platform,
        ]);
    }

    public function addContext(
        LtiPlatform $platform,
        AddContextToLtiPlatformRequest $request,
    ): RedirectResponse {
        $context = Context::where('id', $request->validated('context'))
            ->firstOrFail();

        $platform->contexts()->attach($context, [
            'role' => ContentRole::from($request->validated('role')),
        ]);

        return redirect()->back()
            ->with('alert', trans('messages.context-added-to-lti-platform'));
    }
}
