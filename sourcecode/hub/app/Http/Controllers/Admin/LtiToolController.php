<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLtiToolRequest;
use App\Models\LtiTool;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use function redirect;

final class LtiToolController extends Controller
{
    public function index(): View
    {
        $tools = LtiTool::withCount('resources')->get();

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

        $request->session()->flash('success', 'We added the LTI thing innit');

        return redirect()->route('admin.lti-tools.index');
    }
}
