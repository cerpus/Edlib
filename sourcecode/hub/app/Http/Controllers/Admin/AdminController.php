<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RebuildContentIndex;
use App\Models\LtiToolExtra;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use function back;
use function view;

final class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
           'toolExtras' => LtiToolExtra::forAdmins()->get(),
        ]);
    }

    public function rebuildContentIndex(): RedirectResponse
    {
        RebuildContentIndex::dispatch();

        return back()
            ->with('alert', trans('messages.alert-rebuilding-content-index'));
    }
}
