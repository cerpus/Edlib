<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

interface LtiTypeInterface
{
    public function doShow($id, $context, $preview = false): View|string;

    public function create(Request $request): View;

    public function edit(Request $request, $id): View;
}
