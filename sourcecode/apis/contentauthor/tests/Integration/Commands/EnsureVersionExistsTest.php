<?php

namespace Tests\Integration\Commands;

use App\Article;
use App\Content;
use App\ContentVersions;
use App\H5PContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EnsureVersionExistsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_articles(): void
    {
        // Not versioned
        $noVersion = Article::factory(2)->create([
            'version_id' => null,
        ]);
        // With version
        $versioned = Article::factory(2)->create()->each(function (Article $article) {
            ContentVersions::factory()->create([
                'id' => $article->version_id,
                'content_id' => $article->id,
                'content_type' => Content::TYPE_ARTICLE,
            ]);
        });
        // Version record missing
        $missing = Article::factory(2)->create();
        // Unconnected version
        $unconnectedVersions = collect();
        $notConnected = Article::factory(2)->create([
            'version_id' => null,
        ])->each(function (Article $article) use ($unconnectedVersions) {
            $unconnectedVersions->add(ContentVersions::factory()->create([
                'content_id' => $article->id,
                'content_type' => Content::TYPE_ARTICLE,
            ]));
        });

        $this->artisan('cerpus:ensure-version --debug --skip-h5p')
            ->expectsOutput('Running in debug mode')
            ->expectsOutput('Processing chunk with 8 articles')

            ->expectsOutput(sprintf('Article %s is not versioned', $noVersion->first()->id))
            ->expectsOutput(sprintf('Article %s is not versioned', $noVersion->last()->id))

            ->expectsOutput(sprintf('Article %s is good', $versioned->first()->id))
            ->expectsOutput(sprintf('Article %s is good', $versioned->last()->id))

            ->expectsOutput(sprintf('Article %s has missing version %s', $missing->first()->id, $missing->first()->version_id))
            ->expectsOutput(' - Setting version id to null')
            ->expectsOutput(sprintf('Article %s has missing version %s', $missing->last()->id, $missing->last()->version_id))
            ->expectsOutput(' - Setting version id to null')

            ->expectsOutput(sprintf('Article %s has unconnected version %s', $notConnected->first()->id, $unconnectedVersions->first()->id))
            ->expectsOutput(' - Updating version id to ' . $unconnectedVersions->first()->id)
            ->expectsOutput(sprintf('Article %s has unconnected version %s', $notConnected->last()->id, $unconnectedVersions->last()->id))
            ->expectsOutput(' - Updating version id to ' . $unconnectedVersions->last()->id)

            ->expectsOutput('Skipping H5Ps')
            ->expectsOutput('Couldn\'t find version for 0 h5ps and 2 articles')
        ;

        $this->assertDatabaseHas('articles', [
            'id' => $missing->first()->id,
            'version_id' => null,
        ]);
        $this->assertDatabaseHas('articles', [
            'id' => $missing->last()->id,
            'version_id' => null,
        ]);
        $this->assertDatabaseHas('articles', [
            'id' => $notConnected->first()->id,
            'version_id' => $unconnectedVersions->first()->id,
        ]);
        $this->assertDatabaseHas('articles', [
            'id' => $notConnected->last()->id,
            'version_id' => $unconnectedVersions->last()->id,
        ]);
    }

    public function test_h5p(): void
    {
        // Not versioned
        $noVersion = H5PContent::factory(2)->create();
        // With version
        $versioned = H5PContent::factory(2)->create()->each(function (H5PContent $h5PContent) {
            $version = ContentVersions::factory()->create([
                'content_id' => $h5PContent->id,
                'content_type' => Content::TYPE_H5P,
            ]);
            $h5PContent->version_id = $version->id;
            $h5PContent->saveQuietly();
        });

        // Version record missing
        $missing = H5PContent::factory(2)->create()->each(function (H5PContent $h5PContent) {
            $h5PContent->version_id = $this->faker->uuid;
            $h5PContent->saveQuietly();
        });
        // Unconnected version
        $unconnectedVersions = collect();
        $notConnected = H5PContent::factory(2)->create([
            'version_id' => null,
        ])->each(function (H5PContent $article) use ($unconnectedVersions) {
            $unconnectedVersions->add(ContentVersions::factory()->create([
                'content_id' => $article->id,
                'content_type' => Content::TYPE_H5P,
            ]));
        });

        $this->artisan('cerpus:ensure-version --debug --skip-article')
            ->expectsOutput('Processing chunk with 8 H5Ps')

            ->expectsOutput(sprintf('H5P %s is not versioned', $noVersion->first()->id))
            ->expectsOutput(sprintf('H5P %s is not versioned', $noVersion->last()->id))

            ->expectsOutput(sprintf('H5P %s is good', $versioned->first()->id))
            ->expectsOutput(sprintf('H5P %s is good', $versioned->last()->id))

            ->expectsOutput(sprintf('H5P %s has missing version %s', $missing->first()->id, $missing->first()->version_id))
            ->expectsOutput(' - Setting version id to null')
            ->expectsOutput(sprintf('H5P %s has missing version %s', $missing->last()->id, $missing->last()->version_id))
            ->expectsOutput(' - Setting version id to null')

            ->expectsOutput(sprintf('H5P %s has unconnected version %s', $notConnected->first()->id, $unconnectedVersions->first()->id))
            ->expectsOutput(' - Updating version id to ' . $unconnectedVersions->first()->id)
            ->expectsOutput(sprintf('H5P %s has unconnected version %s', $notConnected->last()->id, $unconnectedVersions->last()->id))
            ->expectsOutput(' - Updating version id to ' . $unconnectedVersions->last()->id)

            ->expectsOutput('Couldn\'t find version for 2 h5ps and 0 articles')
        ;

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $missing->first()->id,
            'version_id' => null,
        ]);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $missing->last()->id,
            'version_id' => null,
        ]);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $notConnected->first()->id,
            'version_id' => $unconnectedVersions->first()->id,
        ]);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $notConnected->last()->id,
            'version_id' => $unconnectedVersions->last()->id,
        ]);
    }
}
