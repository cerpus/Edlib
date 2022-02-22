<?php

namespace App\Messaging\Handlers;

use App\H5PContent;
use App\H5PContentsUserData;
use App\H5PFile;
use Cerpus\LaravelRabbitMQPubSub\Facades\RabbitMQPubSub;
use Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSubConsumerHandler;

class AuthMigrationExecute implements RabbitMQPubSubConsumerHandler
{
    public function consume(string $dataString)
    {
        $data = json_decode($dataString, true);
        $h5pContentsCount = 0;
        $h5pContentsUserDataCount = 0;
        $h5pFileCount = 0;

        foreach ($data['userIds'] as $userId) {
            $h5pContentsCount += H5PContent::where('user_id', $userId['from'])->update([
                'user_id' => $userId['to']
            ]);
            $h5pContentsUserDataCount += H5PContentsUserData::where('user_id', $userId['from'])->update([
                'user_id' => $userId['to']
            ]);
            $h5pFileCount += H5PFile::where('user_id', $userId['from'])->update([
                'user_id' => $userId['to']
            ]);
        }

        RabbitMQPubSub::publish('auth_migration_execute_done', json_encode([
            'id' => $data["id"],
            'apiName' => 'contentauthor',
            'tableName' => 'h5p_contents',
            'rowsUpdated' => $h5pContentsCount,
        ]));
        RabbitMQPubSub::publish('auth_migration_execute_done', json_encode([
            'id' => $data["id"],
            'apiName' => 'contentauthor',
            'tableName' => 'h5p_contents_user_data',
            'rowsUpdated' => $h5pContentsUserDataCount,
        ]));
        RabbitMQPubSub::publish('auth_migration_execute_done', json_encode([
            'id' => $data["id"],
            'apiName' => 'contentauthor',
            'tableName' => 'h5p_files',
            'rowsUpdated' => $h5pFileCount,
        ]));
    }
}
