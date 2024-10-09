<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLtiToolExtraRequest;
use App\Http\Requests\StoreLtiToolRequest;
use App\Models\LtiTool;
use App\Models\LtiToolExtra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

use function redirect;
use function trans;
use function url;

final class LtiToolController extends Controller
{
    public function index(): View
    {
        $tools = LtiTool::withCount('contentVersions')->paginate();

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

    public function addExtra(LtiTool $tool): Response
    {
        return response()->view('admin.lti-tools.add-extra', [
            'tool' => $tool,
        ]);
    }

    public function storeExtra(LtiTool $tool, StoreLtiToolExtraRequest $request): Response
    {
        $tool->extras()->create($request->validated());

        return redirect()->route('admin.lti-tools.index')
            ->with('alert', trans('messages.alert-lti-tool-extra-added', [
                'tool' => $tool->name,
            ]));
    }

    public function removeExtra(LtiTool $tool, LtiToolExtra $extra, Request $request): Response
    {
        $extra->delete();

        $request->session()->flash('alert', trans('messages.alert-lti-tool-extra-removed', [
            'name' => $extra->name,
            'tool-name' => $tool->name,
        ]));

        if ($request->ajax()) {
            return response()->noContent()->header('HX-Redirect', url()->previous());
        }

        return redirect()->back();
    }
}
