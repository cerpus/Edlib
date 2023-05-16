<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreLtiPlatformRequest;
use App\Models\LtiPlatform;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
}
