<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

use function to_route;

class LtiController
{
    public function select(): RedirectResponse
    {
        return to_route('content.index');
    }
}
