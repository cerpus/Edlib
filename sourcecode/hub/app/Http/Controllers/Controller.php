<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

use function abort;
use function app;
use function assert;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    public function getUser(): User
    {
        $guard = app()->make(Guard::class);

        if (!$guard->check()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $user = $guard->user();
        assert($user instanceof User);

        return $user;
    }
}
