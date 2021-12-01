<?php

namespace App\Http\Controllers;

use App\Http\Requests\GDPRDeleteUserRequest;
use App\Models\Application;
use App\Models\GdprRequest;
use Cerpus\LaravelRabbitMQPubSub\Facades\RabbitMQPubSub;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class GDPRController extends Controller
{
    /**
     * @throws Throwable
     */
    public function deleteUser(GDPRDeleteUserRequest $request): Response
    {
        $application = Application::find(Auth::user()->id);

        $existingRequests = GdprRequest::whereRequestId($request->get('requestId'))->whereApplicationId($application->id)->first();

        if ($existingRequests) {
            return new JsonResponse([
                'message' => 'Deletion request with request id ' . $request->get('requestId') . ' has already been submitted',
                409
            ]);
        }

        $gdprRequest = $application->gdprRequests()->create([
            'request_id' => $request->get('requestId'),
            'user_id' => $request->get('userId', null),
        ]);

        $requestId = $gdprRequest->id;

        RabbitMQPubSub::publish('edlib_gdpr_delete_request', json_encode([
            'userId' => $gdprRequest->userId,
            'emails' => $request->get('emails', []),
            'requestId' => $gdprRequest->id,
            'userDataForOverride' => [
                'firstName' => 'GDPR ' . $gdprRequest->id,
                'lastName' => $gdprRequest->requestId,
                'email' => "gdpr-anon-$requestId@edlib.com",
            ]
        ]));

        return new JsonResponse([
            'started' => true,
        ], Response::HTTP_ACCEPTED);
    }
}
