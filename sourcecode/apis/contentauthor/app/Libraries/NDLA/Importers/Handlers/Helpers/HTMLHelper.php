<?php

namespace App\Libraries\NDLA\Importers\Handlers\Helpers;


trait HTMLHelper
{
    protected function addHtml($content)
    {
        return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0//EN\" \"http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd\">
        <html version=\"XHTML 1.0\" xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"nb\" xmlns:og=\"http://opengraphprotocol.org/schema/\" dir=\"ltr\" xmlns:rdfs=\"http://www.w3.org/2000/01/rdf-schema#\" xmlns:skos=\"http://www.w3.org/2004/02/skos/core#\">
        <head>
            <title>Import</title>
        </head>
        <body>$content</body>
        </html>";
    }

    protected function getBody($html)
    {
        preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $matches);

        return $matches[1];
    }

    protected function addHtml5($content, $language = 'en')
    {
        return "<!doctype html>
        <html lang=\"$language\">
        <head>
            <title>Import</title>
        </head>
        <body>
        $content
        </body>
        </html>";
    }

    protected function getNodeAttribute($node, $attribute)
    {
        $value = false;
        $attribute = mb_strtolower($attribute);
        if ($node->hasAttribute($attribute)) {
            $value = $node->getAttribute($attribute);
        }

        return $value;
    }
}
