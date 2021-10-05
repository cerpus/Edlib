<?php

namespace App\Listeners\H5P\Copy;

use App\Events\H5PWasCopied;
use App\Http\Libraries\License;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleLicensing
{
    protected $license;

    public function __construct(License $license)
    {
        $this->license = $license;
    }

    public function handle(H5PWasCopied $event)
    {
        try {
            $originalH5P = $event->originalH5P->fresh();
            $newH5P = $event->newH5P->fresh();

            $parentLicense = $this->license->getLicense($originalH5P->id);// Since we are copying, we should always have a parent.
            $licenseContent = $this->license->getOrAddContent($newH5P);
            if ($licenseContent && $parentLicense) {
                $this->license->setLicense($parentLicense, $newH5P->id);
                $newH5P->license = $parentLicense;
                $newH5P->save();
            }
        } catch (Exception $e) {
            Log::error(__METHOD__ . ': Unable to add License to H5P ' . $newH5P->id . '. ' . $e->getCode() . ': ' . $e->getMessage());
        }
    }
}
