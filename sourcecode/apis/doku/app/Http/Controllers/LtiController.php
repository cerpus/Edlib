<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final class LtiController
{
    public function edit(): View
    {
        return view('lti');
    }
}
