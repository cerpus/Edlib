<?php

namespace App\Listeners\Article\Copy;

use App\Events\Event;
use App\Http\Libraries\License;

class HandleLicensing
{
    protected $license;

    public function __construct(License $license)
    {
        $this->license = $license;
    }

    public function handle(Event $event)
    {
        try {
            $article = $event->article->fresh();

            $parentLicense = $this->license->getLicense($article->parent->id);// Since we are copying, we should always have a parent.
            $licenseContent = $this->license->getOrAddContent($article);
            if ($licenseContent && $parentLicense) {
                $this->license->setLicense($parentLicense, $article->id);
            }
        } catch (Exception $e) {
            Log::error(__METHOD__ . ': Unable to add License to article ' . $article->id . '. ' . $e->getCode() . ': ' . $e->getMessage());
        }
    }
}
