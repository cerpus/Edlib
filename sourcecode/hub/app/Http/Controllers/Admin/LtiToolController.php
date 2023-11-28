<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLtiToolRequest;
use App\Models\LtiTool;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class LtiToolController extends Controller
{
    public function index(): View
    {
        $tools = LtiTool::withCount('contentVersions')->get();

        return view('admin.lti-tools.index', [
            'tools' => $tools,
        ]);
    }

    public function add(): View
    {
        return view('admin.lti-tools.add');
    }

    public function store(StoreLtiToolRequest $request): RedirectResponse
    {
        LtiTool::create($request->validated());

        return to_route('admin.lti-tools.index')
            ->with('alert', trans('messages.alert-lti-tool-added'));
    }

    public function destroy(LtiTool $tool): RedirectResponse
    {
        $tool->delete();

        return redirect()->back()
            ->with('alert', trans('messages.alert-lti-tool-removed', [
                'name' => $tool->name,
            ]));
    }
}
