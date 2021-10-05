<?php
/**
 * Created by PhpStorm.
 * User: oddaj
 * Date: 7/14/16
 * Time: 8:15 AM
 */

namespace App\Libraries\NDLA\Importers\Handlers\Article;

use App\Libraries\NDLA\Importers\Handlers\Helpers\NdlaUrlHelper;
use Log;
use App\Article;
use App\NdlaIdMapper;
use App\Libraries\NDLA\Importers\Handlers\Helpers\HTMLHelper;

class RelinkH5PIframes
{
    use HTMLHelper;

    private function libXmlUseInternalErrors($reportError)
    {
        libxml_use_internal_errors($reportError);
    }

    public function handle(Article $article)
    {
        $this->libXmlUseInternalErrors(true);
        $dom = new \DOMDocument();
        $fullContent = $this->addHtml($article->content);
        $dom->loadHTML($fullContent);
        $iframes = $dom->getElementsByTagName('iframe');
        if (count($errors = libxml_get_errors()) > 0) {
            Log::debug(sprintf("Node %s has %d errors in the markup. These are: %s", $article->node_id, count($errors),
                var_export($errors, true)));
        }
        $this->libXmlUseInternalErrors(false);

        foreach ($iframes as $iframe) {
            $class = $iframe->getAttribute('class');
            if (stripos($class, 'h5p-iframe') === false) {
                continue;
            }
            $iframeId = $iframe->getAttribute('id');
            $ndlaId = $this->getNdlaId($iframeId);
            if ($ndlaId === false) { // Not a link to ndla content
                continue;
            }
            /** @var NdlaIdMapper $oerMap */
            $oerMap = $this->getOERNdlaMapFromNdlaId($ndlaId);
            if (!$oerMap) { // ndlaId is not imported, link to ndla
                $url = NdlaUrlHelper::ndlaUrlForId($ndlaId);
                $iframe->setAttribute('src', $url);
                if (!empty($article->id) && !empty($iframeId) && !empty($url)) {
                    Log::debug('Article ' . $article->id . ' iframe id: ' . $iframeId . ' Rewriting iframe url to:' . $url);
                }
                continue;
            }
            $linkUrl = $oerMap->getOerLink();
            if (!$linkUrl) {
                continue;
            }
            if (!empty($article->id) && !empty($iframeId) && !empty($linkUrl)) {
                Log::debug('Article ' . $article->id . ' iframe id: ' . $iframeId . ' Rewriting iframe url to:' . $linkUrl);
            }
            $iframe->setAttribute('src', $linkUrl );
            $iframe->setAttribute('class', 'oerlearningorg_resource');
        }

        return $this->getBody($dom->saveHTML());
    }

    private function getNdlaId($iframeId)
    {
        $elements = explode('-', $iframeId);
        $hasElements = (count($elements) > 1);
        if ($hasElements) {
            return end($elements);
        }
        return false;
    }

    private function getOERNdlaMapFromNdlaId($ndlaId)
    {
        $result = NdlaIdMapper::where('ndla_id', $ndlaId)->first();
        return $result;
    }


}
