<?php

namespace App\Messaging\Handlers;

use App\H5PContent;
use App\H5PContentsUserData;
use App\H5PFile;
use Cerpus\LaravelRabbitMQPubSub\Facades\RabbitMQPubSub;
use Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSubConsumerHandler;

class AuthMigrationGetFeedback implements RabbitMQPubSubConsumerHandler
{
    public function consume(string $dataString)
    {
        $data = json_decode($dataString, true);

        RabbitMQPubSub::publish('auth_migration_info_feedback', json_encode([
            'id' => $data["id"],
            'tables' => [
                [
                    "apiName" => 'contentauthor',
                    "tableName" => 'h5p_contents',
                    "rowCount" => H5PContent::whereIn('user_id', $data['userIds'])->count()
                ],
                [
                    "apiName" => 'contentauthor',
                    "tableName" => 'h5p_contents_user_data',
                    "rowCount" => H5PContentsUserData::whereIn('user_id', $data['userIds'])->count()
                ],
                [
                    "apiName" => 'contentauthor',
                    "tableName" => 'h5p_files',
                    "rowCount" => H5PFile::whereIn('user_id', $data['userIds'])->count()
                ]
            ]
        ]));
    }
}
