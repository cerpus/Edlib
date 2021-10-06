<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use App\Article;
use Illuminate\Support\Str;

class Files extends BaseHandler
{
    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing Files");

        $document = $this->getDom();

        $embedNodes = $document->getElementsByTagName('div');
        $fileCollectionsCount = 0;
        $processedFilesCount = 0;
        $failedFileCount = 0;
        /** @var \DOMNode $embedNode */
        foreach ($embedNodes as $embedNode) {
            /* Example:
            <div data-type="file">
                <embed data-alt="Arbeidslogg og egenvurdering" data-path="files/172663/arbeidslogg_og_egenvurdering_bm.doc" data-resource="file" data-title="Arbeidslogg og egenvurdering" data-type="doc" data-url="https://api.ndla.no/files/172663/arbeidslogg_og_egenvurdering_bm.doc">
                <embed data-alt="Arbeidslogg og egenvurdering" data-path="files/172663/arbeidslogg_og_egenvurdering_bm.odt" data-resource="file" data-title="Arbeidslogg og egenvurdering" data-type="odt" data-url="https://api.ndla.no/files/172663/arbeidslogg_og_egenvurdering_bm.odt">
            </div>
            */
            if ($this->isFileCollection($embedNode)) {
                if ($embedNode->hasChildNodes()) {
                    /** @var \DOMNode $attachmentEmbed */
                    $attachmentList = [];

                    foreach ($embedNode->childNodes as $attachmentEmbed) {
                        if ($this->isFileEmbed($attachmentEmbed)) {
                            $li = $document->createElement('li');
                            $li->setAttribute('class', 'ndla-attachment-li');

                            $a = $document->createElement('a');
                            $a->setAttribute('href', $attachmentEmbed->getAttribute('data-url'));
                            $fileName = last(explode('/', $attachmentEmbed->getAttribute('data-url')));
                            $a->setAttribute('download', $fileName);
                            $a->setAttribute('class', 'ndla-attachment-a ndla-attachemnt-a-' . $attachmentEmbed->getAttribute('data-type'));

                            $linkTextNode = $document->createTextNode($attachmentEmbed->getAttribute('data-title') . ' (' . $attachmentEmbed->getAttribute('data-type') . ')');
                            $a->appendChild($linkTextNode);

                            $li->appendChild($a);

                            $attachmentList[] = $li;

                            $processedFilesCount++;
                        }
                    }

                    $ul = null;
                    $div = null;
                    if ($attachmentList) {
                        $ul = $document->createElement('ul');
                        $ul->setAttribute('class', 'ndla-attachment-ul');

                        foreach ($attachmentList as $attachment) {
                            $ul->appendChild($attachment);
                        }

                        $div = $document->createElement('div');
                        $div->setAttribute('class', 'ndla-attachment-div');

                        $h2 = $document->createElement('h2');
                        $h2->setAttribute('class', 'ndla-attachment-h2');
                        $linkTextNode = $document->createTextNode('Filer');
                        $h2->appendChild($linkTextNode);

                        $div->appendChild($h2);
                        $div->appendChild($ul);

                        $fileCollectionsCount++;
                    }

                    if ($div) {
                        try {
                            $embedNode->parentNode->replaceChild($div, $embedNode);
                        } catch (\Throwable $t) {
                            $failedFileCount++;
                        }
                    }
                }
            }
        }

        if ($processedFilesCount > 0) {
            $this->saveContent($document);
        }
        $message = "File: Attached $processedFilesCount " . Str::plural('file', $processedFilesCount) .
            " into $fileCollectionsCount file " . Str::plural('collection', $fileCollectionsCount) .
            " $failedFileCount " . Str::plural('attachment', $failedFileCount) . " failed to embed.";

        if ($failedFileCount) {
            $this->error($message);
        } else {
            $this->debug($message);
        }

        return $this->article;
    }

    protected function isFileEmbed($node): bool
    {
        if (!$isResource = $node->hasAttribute('data-resource')) {
            return false;
        }

        $isFileResource = $isResource && (strtolower($node->getAttribute('data-resource')) === 'file');

        return $isResource && $isFileResource;
    }

    /**
     * @param $node
     * @return bool
     */
    protected function isFileCollection($node): bool
    {
        return $node->hasAttribute('data-type') && $node->getAttribute('data-type') === 'file';
    }


}
