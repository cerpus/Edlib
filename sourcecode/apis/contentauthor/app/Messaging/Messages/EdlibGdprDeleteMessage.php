<?php

namespace App\Messaging\Messages;

use Cerpus\LaravelRabbitMQPubSub\Facades\RabbitMQPubSub;

class EdlibGdprDeleteMessage
{
    public $requestId;
    public $userId;
    public $emails;

    public function __construct(array $data)
    {
        $this->requestId = $data['requestId'];
        $this->userId = $data['userId'];
        $this->emails = $data['emails'] ?? [];
    }

    public function stepCompleted(string $stepName, string $message = null): void
    {
        RabbitMQPubSub::publish('edlib_gdpr_delete_request_feedback', json_encode([
            'serviceName' => 'contentauthor',
            'requestId' => $this->requestId,
            'stepName' => $stepName,
            'message' => $message
        ]));
    }
}
