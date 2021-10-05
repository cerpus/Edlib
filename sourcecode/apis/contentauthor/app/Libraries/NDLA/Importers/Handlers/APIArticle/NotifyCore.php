<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;
use App\NdlaIdMapper;
use App\Libraries\NDLA\Notice\Core;
use App\Libraries\NDLA\Importers\Handlers\Helpers\ArticleHash;

class NotifyCore extends BaseHandler
{
    use ArticleHash;

    protected $idMapper;

    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing Core notification");

        $this->idMapper = NdlaIdMapper::articleByNdlaIdAndLanguage($this->jsonArticle->id, ($this->jsonArticle->title->language ?? 'nb'));
        if (!$this->idMapper) {
            $this->idMapper = NdlaIdMapper::create([
                    'ndla_id' => $this->jsonArticle->id,
                    'ca_id' => $this->article->id,
                    'type' => 'article',
                    'language_code' => $this->article->getLanguage(),
                    'ndla_checksum' => $this->generateChecksumHash($this->jsonArticle),
                ]
            );
        }

        if (!$this->idMapper->core_id) {
            $coreReporter = resolve(Core::class);

            $coreData = $coreReporter->notify(
                $this->article->id,
                $this->article->node_id,
                $this->article->title,
                'Article');

            if ($coreData !== false) {
                $this->idMapper->core_id = $coreData->id;
                $this->idMapper->launch_url = $coreData->launch;
                $this->idMapper->save();
                $this->debug('ID Map updated with Core id and launch url.');
            } else {
                $this->error('Reporting to Core failed.');
            }
        } else {
            $this->debug('Already in Core, skipping.');
        }

        return $this->article;
    }
}
