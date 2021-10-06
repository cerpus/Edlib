<?php

namespace App\Http\Controllers;


use Cerpus\LaravelAuth\Service\JWTValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class JWTUpdateController extends Controller {
    public function updateJwtEndpoint(Request $request) {
        $jwtService = new JWTValidationService();
        $newJwt = $request->input('jwt', null);
        $validJwt = $newJwt ? $jwtService->validateJwt($newJwt) : null;
        if ($validJwt) {
            Session::put('jwtToken', [
                'context' => $validJwt->getContextName(),
                'raw' => $validJwt->toString(),
                'payload' => $validJwt->getPayload()
            ]);
        } else {
            App::abort(400);
        }
    }
}