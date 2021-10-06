<?php
namespace App\Libraries\NDLA\Importers\Handlers\Article;

use Log;
use App\Article;
use App\Libraries\NDLA\Importers\Handlers\Helpers\HTMLHelper;
use App\Libraries\NDLA\Importers\Handlers\Helpers\NdlaUrlHelper;

class CorrectImageLinks
{
    use HTMLHelper;

    public function handle(Article $article)
    {
        $dom = new \DOMDocument();
        $fullContent = $this->addHtml($article->content);
        $dom->loadXML($fullContent);
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            $src = NdlaUrlHelper::getFullLinkUrl($src);
            $image->setAttribute('src', $src);
            if (!empty($article->id) && !empty($src)) {
                Log::debug('Article ' . $article->id . ': Rewriting img src to:' . $src);
            }
        }

        return $this->getBody($dom->saveHTML());
    }


}
