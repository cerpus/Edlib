<?php

namespace Tests\Unit\Traits;

use App\Article;
use App\Content;
use Cerpus\VersionClient\VersionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\MockLicensingTrait;
use Tests\Traits\MockVersioningTrait;

class RecommendableTraitTest extends TestCase
{
    use RefreshDatabase, MockLicensingTrait, MockVersioningTrait;

    public function testDeterminesTheCorrectActionBasedOnVisibilityAndLicensing()
    {
        $this->setupVersion([
            "getVersion" => function () {
                $vd = app(VersionData::class);
                return $vd;
            },
        ]);
        $this->setUpLicensing("BY", true);

        /** @var Article $article */
        $publishedListedArticle = factory(Article::class)->states(["newly-created", "listed"])->create();
        $this->assertEquals(Content::RE_ACTION_UPDATE_OR_CREATE, $publishedListedArticle->determineREAction());

        // Not listed
        $publishedListedArticle->is_private = true;
        $publishedListedArticle->save();
        $this->assertEquals(Content::RE_ACTION_REMOVE, $publishedListedArticle->determineREAction());

        // Listed again
        $publishedListedArticle->is_private = false;
        $publishedListedArticle->save();
        $this->assertEquals(Content::RE_ACTION_UPDATE_OR_CREATE, $publishedListedArticle->determineREAction());

        // Not copyable
        $this->setUpLicensing("BY-ND", false);
        $this->assertEquals(Content::RE_ACTION_REMOVE, $publishedListedArticle->determineREAction());

        // Not listed and not copyable
        $publishedListedArticle->is_private = true;
        $publishedListedArticle->save();
        $this->assertEquals(Content::RE_ACTION_REMOVE, $publishedListedArticle->determineREAction());
    }

    public function testDeterminesTheCorrectActionPublicChildren()
    {
        $article = factory(Article::class)->states(["newly-created", "listed"])->create(["id" => 1]);
        $childArticle = factory(Article::class)->states(["listed"])->create([
            "id" => 2,
            "parent_id" => $article->id,
            "original_id" => $article->id,
        ]);

        // Versioning has children for the original article
        // and the child should be in the recommendation engine
        $this->setupVersion([
            "getVersion" => function () use ($article, $childArticle) {
                $vd = app(VersionData::class);
                $vd->setId(1);
                $vd->setExternalReference($article->id);

                $child = (object) [
                    "id" => 2,
                    "externalReference" => $childArticle->id,
                    "versionPurpose" => VersionData::UPDATE,
                ];

                $vd->setChildren([$child]);

                return $vd;
            },
        ]);
        $this->setUpLicensing("BY", true);

        // We have public children -> We should remove the content from RE
        $this->assertEquals(Content::RE_ACTION_REMOVE, $article->determineREAction());

        // No (public) children here... -> We should update or create his content in RE
        $this->setupVersion([
            "getVersion" => function () use ($article, $childArticle) {
                $vd = app(VersionData::class);
                $vd->setId(2);
                $vd->setExternalReference($childArticle->id);
                return $vd;
            },
        ]);
        $this->assertEquals(Content::RE_ACTION_UPDATE_OR_CREATE, $childArticle->determineREAction());

        // Unlist the article -> We should remove from RE
        $childArticle->is_private = true;
        $childArticle->save();
        $this->assertEquals(Content::RE_ACTION_REMOVE, $childArticle->determineREAction());
    }
}
