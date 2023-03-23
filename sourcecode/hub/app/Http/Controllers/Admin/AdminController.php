<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

use function view;

final class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index');
    }
}
