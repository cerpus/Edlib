<?php

namespace Tests\Traits;

use App\Libraries\ContentAuthorStorage;

trait ContentAuthorStorageTrait
{
    private ContentAuthorStorage $contentAuthorStorage;

    public function setUpContentAuthorStorage()
    {
        $this->contentAuthorStorage = app(ContentAuthorStorage::class);
    }
}
