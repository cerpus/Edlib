<?php

namespace App\Messaging\Handlers;

use App\Models\GdprRequest;
use Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSubConsumerHandler;
use Illuminate\Support\Facades\Log;

class EdlibGdprDeleteRequestFeedback implements RabbitMQPubSubConsumerHandler
{
    public function consume(string $data)
    {
        $decodedData = json_decode($data, true);

        if (!array_key_exists('serviceName', $decodedData) || !array_key_exists('requestId', $decodedData) || !array_key_exists('stepName', $decodedData)) {
            Log::error('Received invalid data', $decodedData);
            return;
        }

        $gdprRequest = GdprRequest::find($decodedData['requestId']);

        if (!$gdprRequest) {
            Log::error('Request id not found');
            return;
        }

        $gdprRequest->gdprRequestCompletedSteps()->create([
            'service_name' => $decodedData['serviceName'],
            'step_name' => $decodedData['stepName'],
            'message' => $decodedData['message'] ?? null
        ]);
    }
}
