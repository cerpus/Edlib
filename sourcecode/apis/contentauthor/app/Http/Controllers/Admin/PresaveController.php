<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

final class PresaveController
{
    public function index(): View
    {
        return view('admin.presave.index');
    }

    public function runPresave(): RedirectResponse
    {
        Artisan::call('h5p:addPresave');

        return redirect(route('admin.presave.index'))
            ->with('message', 'Presave command run.');
    }
}
