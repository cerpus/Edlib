<?php

namespace App\Gdpr\Handlers;

use App\Messaging\Messages\EdlibGdprDeleteMessage;

interface Processor
{
    public function handle(EdlibGdprDeleteMessage $edlibGdprDeleteMessage);
}
