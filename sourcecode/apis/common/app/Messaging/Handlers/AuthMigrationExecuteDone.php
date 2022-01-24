<?php

namespace App\Messaging\Handlers;

use App\AuthMigration;
use Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSubConsumerHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AuthMigrationExecuteDone implements RabbitMQPubSubConsumerHandler
{
    public function handleError(\Exception $e, $broker): void
    {
        $broker->ackMessage();
        Log::error($e);
    }

    public function consume(string $data)
    {
        $decodedData = json_decode($data, true);

        if (!array_key_exists('id', $decodedData) || !array_key_exists('apiName', $decodedData) || !array_key_exists('tableName', $decodedData) || !array_key_exists('rowsUpdated', $decodedData)) {
            Log::error('Received invalid data', $decodedData);
            return;
        }

        /** @var AuthMigration $authMigrationData */
        $authMigrationData = Cache::get('auth-migration-' . $decodedData['id']);

        if ($authMigrationData == null) {
            Log::error('Received unknown migration id', $decodedData);
            return;
        }

        $authMigrationData->tableDone($decodedData['apiName'], $decodedData['tableName'], $decodedData['rowsUpdated']);

        Cache::put('auth-migration-' . $decodedData['id'], $authMigrationData, now()->addDays(7));
    }
}
