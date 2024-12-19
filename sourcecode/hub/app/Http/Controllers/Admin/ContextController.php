<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreContextRequest;
use App\Models\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

use function redirect;

final readonly class ContextController
{
    public function index(): Response
    {
        $contexts = Context::all();

        return response()->view('admin.contexts.index', [
            'contexts' => $contexts,
        ]);
    }

    public function add(StoreContextRequest $request): RedirectResponse
    {
        $context = new Context();
        $context->fill($request->validated());
        $context->save();

        return redirect()->route('admin.contexts.index');
    }
}
