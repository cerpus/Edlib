<?php

namespace App\Gdpr;

use App\Gdpr\Handlers\Processor;
use App\Gdpr\Handlers\H5PProcessor;
use App\Gdpr\Handlers\GameProcessor;
use App\Gdpr\Handlers\LinkProcessor;
use App\Gdpr\Handlers\ShareProcessor;
use App\Gdpr\Handlers\H5PResultProcessor;
use App\Gdpr\Handlers\ContentLockProcessor;
use Cerpus\Gdpr\Models\GdprDeletionRequest;
use App\Gdpr\Handlers\QuestionSetProcessor;
use App\Gdpr\Handlers\ContextShareProcessor;
use Cerpus\Gdpr\Contracts\GdprDeletionContract;

class Deletion implements GdprDeletionContract
{
    protected $processors = [
        ShareProcessor::class,//
        ContextShareProcessor::class,//
        H5PResultProcessor::class,
        ContentLockProcessor::class,
    ];

    public function delete(GdprDeletionRequest $deletionRequest)
    {
        $deletionRequest->log('processing', "Starting Content Author deletion procedure.");

        foreach ($this->processors as $processor) {
            $worker = new $processor;
            if ($worker instanceof Processor) {
                $worker->handle($deletionRequest);
            } else {
                $deletionRequest->log('warning', get_class($worker) . " does not implement the App\\Gdpr\\Handlers\\Processor interface.");
            }
            unset($worker);
        }
    }
}
