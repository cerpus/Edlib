<?php

namespace Tests\Integration\Commands;

use App\Article;
use App\Game;
use App\H5PContent;
use App\Http\Libraries\License;
use App\Link;
use App\QuestionSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EdlibLIcenseStatsTest extends TestCase
{
    use RefreshDatabase;

    public function testStats()
    {
        Article::factory()->count(1)->create(['license' => '']);
        Article::factory()->count(1)->create(['license' => License::LICENSE_BY]);
        Article::factory()->count(2)->create(['license' => License::LICENSE_BY_NC_ND]);
        Article::factory()->count(3)->create(['license' => License::LICENSE_BY_ND]);
        Article::factory()->count(4)->create(['license' => License::LICENSE_EDLIB]);

        Game::factory()->count(1)->create(['license' => License::LICENSE_BY_NC]);
        Game::factory()->count(3)->create(['license' => License::LICENSE_BY_NC_ND]);
        Game::factory()->count(2)->create(['license' => License::LICENSE_CC0]);
        Game::factory()->count(2)->create(['license' => License::LICENSE_EDLIB]);
        Game::factory()->count(1)->create(['license' => License::LICENSE_PDM]);

        Link::factory()->count(1)->create(['license' => License::LICENSE_BY_NC_SA]);
        Link::factory()->count(2)->create(['license' => License::LICENSE_BY_SA]);
        Link::factory()->count(4)->create(['license' => License::LICENSE_CC0]);
        Link::factory()->count(3)->create(['license' => License::LICENSE_EDLIB]);
        Link::factory()->count(1)->create(['license' => License::LICENSE_PRIVATE]);

        QuestionSet::factory()->count(1)->create(['license' => '']);
        QuestionSet::factory()->count(3)->create(['license' => License::LICENSE_BY]);
        QuestionSet::factory()->count(4)->create(['license' => License::LICENSE_BY_NC]);
        QuestionSet::factory()->count(2)->create(['license' => License::LICENSE_BY_NC_SA]);
        QuestionSet::factory()->count(1)->create(['license' => License::LICENSE_CC0]);

        H5PContent::factory()->count(3)->create(['license' => '']);
        H5PContent::factory()->count(1)->create(['license' => License::LICENSE_BY_ND]);
        H5PContent::factory()->count(4)->create(['license' => License::LICENSE_BY_SA]);
        H5PContent::factory()->count(1)->create(['license' => License::LICENSE_CC0]);
        H5PContent::factory()->count(2)->create(['license' => License::LICENSE_PDM]);

        $this->artisan('edlib:license-stats')
            ->expectsOutput('Articles')
            ->expectsTable(['License', 'Count'], [
                ['license' => '', 'aggregate' => 1],
                ['license' => License::LICENSE_BY, 'aggregate' => 1],
                ['license' => License::LICENSE_BY_NC_ND, 'aggregate' => 2],
                ['license' => License::LICENSE_BY_ND, 'aggregate' => 3],
                ['license' => License::LICENSE_EDLIB, 'aggregate' => 4],
            ])
            ->expectsOutput('Games')
            ->expectsTable(['License', 'Count'], [
                ['license' => License::LICENSE_BY_NC, 'aggregate' => 1],
                ['license' => License::LICENSE_BY_NC_ND, 'aggregate' => 3],
                ['license' => License::LICENSE_CC0, 'aggregate' => 2],
                ['license' => License::LICENSE_EDLIB, 'aggregate' => 2],
                ['license' => License::LICENSE_PDM, 'aggregate' => 1],
            ])
            ->expectsOutput('Links')
            ->expectsTable(['License', 'Count'], [
                ['license' => License::LICENSE_BY_NC_SA, 'aggregate' => 1],
                ['license' => License::LICENSE_BY_SA, 'aggregate' => 2],
                ['license' => License::LICENSE_CC0, 'aggregate' => 4],
                ['license' => License::LICENSE_EDLIB, 'aggregate' => 3],
                ['license' => License::LICENSE_PRIVATE, 'aggregate' => 1],
            ])
            ->expectsOutput('Question sets')
            ->expectsTable(['License', 'Count'], [
                ['license' => '', 'aggregate' => 1],
                ['license' => License::LICENSE_BY, 'aggregate' => 3],
                ['license' => License::LICENSE_BY_NC, 'aggregate' => 4],
                ['license' => License::LICENSE_BY_NC_SA, 'aggregate' => 2],
                ['license' => License::LICENSE_CC0, 'aggregate' => 1],
            ])
            ->expectsOutput('H5P contents')
            ->expectsTable(['License', 'Count'], [
                ['license' => '', 'aggregate' => 3],
                ['license' => License::LICENSE_BY_ND, 'aggregate' => 1],
                ['license' => License::LICENSE_BY_SA, 'aggregate' => 4],
                ['license' => License::LICENSE_CC0, 'aggregate' => 1],
                ['license' => License::LICENSE_PDM, 'aggregate' => 2],
            ])
            ->assertSuccessful();
    }
}
