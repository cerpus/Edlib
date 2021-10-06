<?php

namespace App\Listeners\H5P;

use App\Events\VideoSourceChanged;
use H5PCore;

class HandleVideoSource
{
    private $core;

    /**
     * Create the event listener.
     *
     * @param H5PCore $core
     */
    public function __construct(H5PCore $core)
    {
        $this->core = $core;
    }

    /**
     * Handle the event.
     *
     * @param  VideoSourceChanged $event
     * @return void
     */
    public function handle(VideoSourceChanged $event)
    {
        if (config('h5p.video.enable') === true && config('h5p.video.deleteVideoSourceAfterConvertToStream') === true) {
            $h5pContent = $event->content;
            /** @var \H5PFileStorage $storage */
            $storage = $this->core->fs;
            $storage->removeContentFile($event->file, $h5pContent->id);
        }
    }
}
