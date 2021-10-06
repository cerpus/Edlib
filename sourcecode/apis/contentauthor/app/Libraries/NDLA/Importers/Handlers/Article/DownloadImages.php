<?php
/**
 * Created by PhpStorm.
 * User: oddaj
 * Date: 7/14/16
 * Time: 8:14 AM
 */

namespace App\Libraries\NDLA\Importers\Handlers\Article;


use App\Article;

class DownloadImages
{
    public function handle(Article $article)
    {

        return $article->content;
    }
}