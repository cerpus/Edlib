<?php

use Vinelab\Bowler\Facades\Registrator;

Registrator::queue('ca-EdStep-CollaborationUpdates', 'App\Messaging\Handlers\EdStepCollaborationHandler', [
    'exchangeName' => 'edstep_messages',
    'exchangeType'=> 'topic',
    'bindingKeys' => [
        'edstep.context-shares.update'
    ],
    'pasive' => false,
    'durable' => true,
    'autoDelete' => false,
    'messageTTL' => null
]);
