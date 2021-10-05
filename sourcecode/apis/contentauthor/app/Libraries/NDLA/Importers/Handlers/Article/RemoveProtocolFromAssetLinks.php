<?php
namespace App\Libraries\NDLA\Importers\Handlers\Article;

use Log;
use App\Article;
use App\Libraries\NDLA\Importers\Handlers\Helpers\HTMLHelper;
use App\Libraries\NDLA\Importers\Handlers\Helpers\NdlaUrlHelper;

class RemoveProtocolFromAssetLinks
{
    use HTMLHelper;

    public function handle(Article $article)
    {
        $this->libXmlUseInternalErrors(true);
        $dom = new \DOMDocument();
        $fullContent = $this->addHtml($article->content);
        $dom->loadHTML($fullContent);

        $this->removeSchemeFromUrlInTagAttribute($dom, 'audio');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'embed');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'iframe');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'img');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'input');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'script');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'source');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'track');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'video');

        $this->removeSchemeFromUrlInTagAttribute($dom, 'a', 'href');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'area', 'href');

        $this->removeSchemeFromUrlInTagAttribute($dom, 'blockquote', 'cite');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'del', 'cite');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'ins', 'cite');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'q', 'cite');

        $this->removeSchemeFromUrlInTagAttribute($dom, 'form', 'action');
        $this->removeSchemeFromUrlInTagAttribute($dom, 'object', 'data');

        $theHtml = $dom->saveHTML();

        return $this->getBody($theHtml);
    }

    protected function removeSchemeFromUrlInTagAttribute($dom, $tag, $attribute = 'src')
    {
        $tags = $dom->getElementsByTagName($tag);
        foreach ($tags as $tag) {
            $src = $tag->getAttribute($attribute);
            $urlWithoutScheme = $this->removeSchemeFromUrl($src);
            $tag->setAttribute($attribute, $urlWithoutScheme);
        }
    }

    protected function removeSchemeFromUrl($url)
    {
        $parts = parse_url($url);
        unset($parts['scheme']);
        return NdlaUrlHelper::urlStringFromArray($parts);
    }

    protected function libXmlUseInternalErrors($reportError)
    {
        libxml_use_internal_errors($reportError);
    }

}
