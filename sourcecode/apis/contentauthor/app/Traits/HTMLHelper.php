<?php

namespace App\Traits;

trait HTMLHelper
{
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
