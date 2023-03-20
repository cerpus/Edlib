<?php

namespace App\Http\Controllers;

use App\Models\Content;

class ExplorerController extends Controller
{
    public function index(): mixed
    {
        return view('explorer.index', [
            'contents' => Content::paginate(25),
        ]);
    }
}
