<?php

namespace App\Http\Controllers;

use App\Auth\Jwt\JwtException;
use App\Auth\Jwt\JwtDecoderInterface;
use App\Http\Requests\JwtRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JWTUpdateController extends Controller
{
    public function __construct(private readonly JwtDecoderInterface $jwtDecoder)
    {
    }

    public function updateJwtEndpoint(JwtRequest $request): void
    {
        $newJwt = $request->validated('jwt');

        try {
            $this->jwtDecoder->getVerifiedPayload($newJwt);
        } catch (JwtException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        $request->getSession()->set('jwtToken', ['raw' => $newJwt]);
    }
}
