<?php

namespace App\Http\Controllers;

use Anik\Amqp\Exchanges\Fanout;
use Anik\Laravel\Amqp\Facades\Amqp;
use App\Http\Requests\GDPRDeleteUserRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class GDPRController extends Controller
{
    /**
     * @throws Throwable
     */
    public function deleteUser(GDPRDeleteUserRequest $request): Response
    {
        $fanout = new Fanout('edlib_gdpr_delete_request');
        $fanout->setDeclare(true);
        $requestId = $request->get('requestId');

        Amqp::publish(json_encode([
            'userId' => $request->get('userId'),
            'emails' => $request->get('emails', []),
            'requestId' => $requestId,
            'userDataForOverride' => [
                'firstName' => 'GDPR Anon',
                'lastName' => $requestId,
                'email' => "gdpr-anon-$requestId@edlib.com",
            ]
        ]), '', $fanout);

        return new JsonResponse([
            'started' => true,
        ], Response::HTTP_ACCEPTED);
    }
}
