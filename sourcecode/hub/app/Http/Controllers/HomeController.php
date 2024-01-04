<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Content;
use Symfony\Component\HttpFoundation\Response;

final readonly class HomeController
{
    public function __invoke(): Response
    {
        if (auth()->check()) {
            return redirect()->route('content.index');
        }

        return response()->view('home', [
            'contents' => Content::findShared(limit: 6)->get(),
        ]);
    }
}
