<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RebuildContentIndex;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use function back;
use function view;

final class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index');
    }

    public function rebuildContentIndex(): RedirectResponse
    {
        RebuildContentIndex::dispatch();

        return back()
            ->with('alert', trans('messages.alert-rebuilding-content-index'));
    }
}
