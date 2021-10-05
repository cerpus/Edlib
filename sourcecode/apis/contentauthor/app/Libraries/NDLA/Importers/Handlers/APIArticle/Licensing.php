<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;
use App\Http\Libraries\License;

class Licensing extends BaseHandler
{
    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing License");

        if (config('feature.licensing')) {
            $license = $this->jsonArticle->copyright->license->license ?? null;

            if ($license) {
                /** @var License $licenseClient */
                $licenseClient = resolve(License::class);
                $licenseContent = $licenseClient->getOrAddContent($this->article);
                if ($licenseContent) {
                    $license = str_replace('-4.0', '', $license);
                    $licensingResponse = $licenseClient->setLicense($license, $this->article->id);

                    $setLicense = 'unknown';

                    $theLicense = $licensingResponse->licenses ?? null;
                    if (!empty($theLicense) && is_array($theLicense)) {
                        $setLicense = implode(',', $theLicense);
                    }

                    $this->debug('License: Set to ' . $setLicense);
                } else {
                    $this->error('License: Unable to fetch or create licensing info.');
                }
            }
        } else {
            $this->debug('License: Licensing disabled.');
        }

        return $this->article;
    }
}
