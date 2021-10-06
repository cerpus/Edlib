<?php

namespace App\Gdpr\Handlers;

use Cerpus\Gdpr\Models\GdprDeletionRequest;

interface Processor
{
    public function handle(GdprDeletionRequest $deletionRequest);
}
