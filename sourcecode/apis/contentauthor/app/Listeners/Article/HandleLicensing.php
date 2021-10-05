<?php

namespace App\Listeners\Article;

use App\ACL\ArticleAccess;
use App\Http\Libraries\License;
use App\Events\ArticleWasSaved;

class HandleLicensing
{
    use ArticleAccess;

    protected $license;

    public function handle(ArticleWasSaved $event)
    {
        try {
            $article = $event->article->fresh();
            $request = $event->request;

            $this->license = app()->make(License::class);
            $licenseContent = $this->license->getOrAddContent($article);
            if ($licenseContent) {
                $this->license->setLicense($request->input('license', 'BY'), $article->id);
            }
        } catch (Exception $e) {
            Log::error(__METHOD__ . ': Unable to add License to article ' . $article->id . '. ' . $e->getCode() . ': ' . $e->getMessage());
        }
    }
}
