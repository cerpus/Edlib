<?php

namespace Tests\Unit\Libraries;

use App\Article;
use App\Game;
use App\H5PContent;
use App\Libraries\ModelRetriever;
use App\QuestionSet;
use PHPUnit\Framework\TestCase;

class ModelRetrieverTest extends TestCase
{
    /**
     * @test
     */
    public function getModelFromGroup_returnsH5PcontentClass()
    {
        $this->assertEquals(H5PContent::class, ModelRetriever::getModelFromGroup("h5p"));
        $this->assertEquals(H5PContent::class, ModelRetriever::getModelFromGroup("H5P"));
    }

    /**
     * @test
     */
    public function getModelFromGroup_returnsArticleClass()
    {
        $this->assertEquals(Article::class, ModelRetriever::getModelFromGroup("Article"));
        $this->assertEquals(Article::class, ModelRetriever::getModelFromGroup("article"));
    }

    /**
     * @test
     */
    public function getModelFromGroup_returnsGameClass()
    {
        $this->assertEquals(Game::class, ModelRetriever::getModelFromGroup("Game"));
        $this->assertEquals(Game::class, ModelRetriever::getModelFromGroup("game"));
    }

    /**
     * @test
     */
    public function getModelFromGroup_returnsQuestionSetClass()
    {
        $this->assertEquals(QuestionSet::class, ModelRetriever::getModelFromGroup("QuestionSet"));
        $this->assertEquals(QuestionSet::class, ModelRetriever::getModelFromGroup("questionset"));
    }

    /**
     * @test
     */
    public function getModelFromContentType_returnsH5pContentClass()
    {
        $this->assertEquals(H5PContent::class, ModelRetriever::getModelFromContentType("h5p.test"));
        $this->assertEquals(H5PContent::class, ModelRetriever::getModelFromContentType("H5P.test"));
        $this->assertEquals(H5PContent::class, ModelRetriever::getModelFromContentType("random"));
    }

    /**
     * @test
     */
    public function getModelFromContentType_returnsQustionsetClass()
    {
        $this->assertEquals(QuestionSet::class, ModelRetriever::getModelFromContentType("questionset"));
        $this->assertEquals(QuestionSet::class, ModelRetriever::getModelFromContentType("QUESTIONSET"));
    }
}
