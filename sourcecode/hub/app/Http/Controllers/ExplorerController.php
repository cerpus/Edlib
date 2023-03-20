<?php

namespace App\Http\Controllers;

use App\Models\Content;

class ExplorerController extends Controller
{
    public function index()
    {
        return view('explorer.index', [
            'contents' => Content::paginate(25),
        ]);
    }
}
