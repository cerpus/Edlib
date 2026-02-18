<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContentFilter;
use App\Models\Content;
use Symfony\Component\HttpFoundation\Response;

final readonly class HomeController
{
    public function __invoke(ContentFilter $request): Response
    {
        if (auth()->check()) {
            return redirect()->route('content.index');
        }

        return response()->view('home', [
            'contents' => $request->getWithModel(
                Content::findShared()->orderBy('created_at'),
                limit: 6,
            ),
        ]);
    }
}
