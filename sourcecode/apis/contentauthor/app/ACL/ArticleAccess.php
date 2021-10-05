<?php
namespace App\ACL;

use App\ArticleCollaborator;
use Session;
use App\Article;

trait ArticleAccess
{

    private function canUpdateArticle(Article $article)
    {
        if (!Session::has('authId')) {
            return false;
        }

        if ($article->isOwner(Session::get('authId'))) {
            return true;
        }

        if($article->isCollaborator()){
            return true;
        }

        if($article->isExternalCollaborator(Session::get('authId'))){
            return true;
        }

        if( $article->isCopyable()){
	        return true;
        }

        return false;
    }

    private function canCreate()
    {
        return Session::has('authId');
    }

    private function canUpdateCollaborators(Article $article, $authId = null)
    {
        if (is_null($authId)) {
            $authId = Session::get('authId');
        }

        return $article->owner_id == $authId;
    }

    private function canUpdatePrivacy(Article $article, $authId = null)
    {
        if (is_null($authId)) {
            $authId = Session::get('authId');
        }

        return $article->owner_id == $authId;
    }

    private function canUpdateLicense(Article $article, $authId = null)
    {
        if (is_null($authId)) {
            $authId = Session::get('authId');
        }

        return $article->owner_id == $authId;
    }


    private function canDeleteArticle(Article $article, $authId = null)
    {
        return false; // No deletes!
    }
}