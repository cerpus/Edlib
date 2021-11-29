<?php

namespace App\Messaging\Handlers;

use App\Gdpr\Handlers\ContentLockProcessor;
use App\Gdpr\Handlers\ContextShareProcessor;
use App\Gdpr\Handlers\H5PResultProcessor;
use App\Gdpr\Handlers\Processor;
use App\Gdpr\Handlers\ShareProcessor;
use App\Messaging\Messages\EdlibGdprDeleteMessage;
use Illuminate\Support\Facades\Log;

class EdlibGdprDeleteRequest
{
    protected $processors = [
        ShareProcessor::class,
        ContextShareProcessor::class,
        H5PResultProcessor::class,
        ContentLockProcessor::class,
    ];

    /**
     * Handle message.
     *
     * @param PhpAmqpLib\Message\AMQPMessage $msg
     */
    public function handle($msg): void
    {
        $edlibGdprDeleteMessage = new EdlibGdprDeleteMessage(json_decode($msg->body, true));

        foreach ($this->processors as $processor) {
            $worker = new $processor;
            if ($worker instanceof Processor) {
                $worker->handle($edlibGdprDeleteMessage);
            } else {
                Log::warning( get_class($worker) . " does not implement the App\\Gdpr\\Handlers\\Processor interface.");
            }
            unset($worker);
        }
    }

    public function handleError(\Exception $e, $broker): void
    {
        $broker->ackMessage();
        Log::error($e);
    }
}
