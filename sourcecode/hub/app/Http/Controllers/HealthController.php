<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\Response;

final class HealthController
{
    public function __invoke(Dispatcher $dispatcher): Response
    {
        $dispatcher->dispatch(new DiagnosingHealth());

        return new Response('ok', 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
