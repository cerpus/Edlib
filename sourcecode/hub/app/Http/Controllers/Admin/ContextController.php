<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AttachContextToContentsRequest;
use App\Http\Requests\StoreContextRequest;
use App\Jobs\AttachContextToContents;
use App\Models\Context;
use Illuminate\Contracts\Bus\Dispatcher;
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

        return redirect()->route('admin.contexts.index')
            ->with('alert', trans('messages.context-added'));
    }

    public function attachToContents(): Response
    {
        $contexts = Context::all()
            ->mapWithKeys(fn(Context $context) => [$context->id => $context->name]);

        return response()->view('admin.contexts.attach-to-contents', [
            'available_contexts' => $contexts,
        ]);
    }

    public function performAttachToContents(
        AttachContextToContentsRequest $request,
        Dispatcher $dispatcher,
    ): RedirectResponse {
        $dispatcher->dispatch(
            new AttachContextToContents($request->getContext()),
        );

        return redirect()->route('admin.index');
    }
}
