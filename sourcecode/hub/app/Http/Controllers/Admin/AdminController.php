<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RebuildContentIndex;
use App\Models\LtiToolExtra;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

use function back;
use function response;
use function route;
use function trans;
use function view;

final class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'toolExtras' => LtiToolExtra::forAdmins()->get(),
        ]);
    }

    public function rebuildContentIndex(Dispatcher $dispatcher, Request $request): Response
    {
        $dispatcher->dispatch(new RebuildContentIndex());

        $request->session()
            ->flash('alert', trans('messages.alert-rebuilding-content-index'));

        if ($request->header('HX-Request')) {
            return response()->noContent()
                ->header('HX-Redirect', route('admin.index'));
        }

        return back()
            ->with('alert', trans('messages.alert-rebuilding-content-index'));
    }
}
