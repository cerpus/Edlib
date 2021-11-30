<?php

namespace App\Http\Controllers;

use App\Http\Requests\GDPRDeleteUserRequest;
use Cerpus\LaravelRabbitMQPubSub\Facades\RabbitMQPubSub;
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
        $requestId = $request->get('requestId');

        RabbitMQPubSub::publish('edlib_gdpr_delete_request', json_encode([
            'userId' => $request->get('userId'),
            'emails' => $request->get('emails', []),
            'requestId' => $requestId,
            'userDataForOverride' => [
                'firstName' => 'GDPR Anon',
                'lastName' => $requestId,
                'email' => "gdpr-anon-$requestId@edlib.com",
            ]
        ]));

        return new JsonResponse([
            'started' => true,
        ], Response::HTTP_ACCEPTED);
    }
}
