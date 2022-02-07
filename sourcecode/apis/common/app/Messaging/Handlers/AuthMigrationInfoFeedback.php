<?php

namespace App\Messaging\Handlers;

use App\AuthMigration;
use Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSubConsumerHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AuthMigrationInfoFeedback implements RabbitMQPubSubConsumerHandler
{
    public function handleError(\Exception $e, $broker): void
    {
        $broker->ackMessage();
        Log::error($e);
    }

    public function consume(string $data)
    {
        $decodedData = json_decode($data, true);

        if (!array_key_exists('id', $decodedData) || !array_key_exists('tables', $decodedData) || !is_array($decodedData['tables'])) {
            Log::error('Received invalid data', $decodedData);
            return;
        }


        /** @var AuthMigration $authMigrationData */
        $authMigrationData = Cache::get('auth-migration-' . $decodedData['id']);

        if ($authMigrationData == null) {
            Log::error('Received unknown migration id', $decodedData);
            return;
        }

        foreach ($decodedData['tables'] as $table) {
            $authMigrationData->addTable($table['apiName'], $table['tableName'], $table['rowCount']);
        }

        Cache::put('auth-migration-' . $decodedData['id'], $authMigrationData, now()->addDays(7));
    }
}
