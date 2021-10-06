<?php

namespace App\Messaging\Handlers;

use Log;
use App\Libraries\Workers\EdStepContextCollaboratorProcessor;

class EdStepCollaborationHandler
{
    /**
     * Handle message.
     *
     * @param PhpAmqpLib\Message\AMQPMessage $msg
     *
     * @return void
     */
    public function handle($msg)
    {
        try {
            $message = json_decode($msg->body);
            $processor = new EdStepContextCollaboratorProcessor($message);
            $processor->process();
            Log::debug("Processed collaboration change for context {$message->contextId}");
            unset($processor);
            unset($message);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Handle error
     *
     * @param \Exception $e
     * @param Vinelab\Bowler\MessageBroker $broker
     *
     * @return void
     */
    public function handleError($e, $broker)
    {
        throw $e;
    }
}
