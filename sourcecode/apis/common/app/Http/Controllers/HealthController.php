<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;

final class HealthController extends Controller
{
    public function get(): Response
    {
        return new Response('ok', Response::HTTP_OK);
    }
}
