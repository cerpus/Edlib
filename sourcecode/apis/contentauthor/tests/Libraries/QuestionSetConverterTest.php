<?php

namespace Tests\Libraries;

use App\Content;
use App\Events\GameWasSaved;
use App\Events\H5PWasSaved;
use App\Gametype;
use App\Http\Libraries\License;
use App\Libraries\DataObjects\ResourceMetadataDataObject;
use App\Libraries\Games\Millionaire\Millionaire;
use App\Libraries\H5P\Packages\QuestionSet as H5PQuestionSet;
use App\Libraries\QuestionSet\QuestionSetConvert;
use App\QuestionSet;
use App\QuestionSetQuestion;
use App\QuestionSetQuestionAnswer;
use H5peditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionSetConverterTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateMillionaireGame(): void
    {
        $this->expectsEvents([GameWasSaved::class]);

        /** @var QuestionSet $questionSet */
        $questionSet = QuestionSet::factory()->create([
            'is_published' => false,
        ]);
        /** @var Gametype $gameType */
        $gameType = Gametype::factory()->create([
            'name' => Millionaire::$machineName,
        ]);
        $resourceMetaObject = ResourceMetadataDataObject::create([
            'license' => License::LICENSE_BY_NC,
            'share' => true,
            'tags' => ['List', 'of', 'tags'],
        ]);

        /** @var QuestionSetConvert $questionsetConverter */
        $questionsetConverter = app(QuestionSetConvert::class);
        list($id, $title, $machineName, $route, $resourceType) = $questionsetConverter->convert(
            Millionaire::$machineName,
            $questionSet,
            $resourceMetaObject
        );

        $this->assertDatabaseHas('games', [
            'id' => $id,
            'title' => $title,
            'license' => License::LICENSE_BY_NC,
            'gametype' => $gameType->id,
        ]);
        $this->assertEquals('Game', $machineName);
        $this->assertEquals(route('game.edit', $id), $route);
        $this->assertEquals(Content::TYPE_GAME, $resourceType);
    }

    public function testCreateH5PQuestionSet(): void
    {
        $this->expectsEvents([H5PWasSaved::class]);

        $h5peditorMock = $this->createMock(H5peditor::class);
        app()->instance(H5peditor::class, $h5peditorMock);
        $h5peditorMock
            ->expects($this->once())
            ->method('processParameters');

        /** @var QuestionSet $questionSet */
        $questionSet = QuestionSet::factory()->create([
            'is_published' => false,
            'license' => License::LICENSE_BY_ND,
        ]);
        /** @var QuestionSetQuestion $question */
        $question = QuestionSetQuestion::factory()->create([
            'question_set_id' => $questionSet->id,
        ]);
        QuestionSetQuestionAnswer::factory()->count(4)->create([
            'question_id' => $question->id,
        ]);
        $resourceMetaObject = ResourceMetadataDataObject::create([
            'license' => License::LICENSE_BY_NC,
            'share' => true,
            'tags' => ['List', 'of', 'tags'],
        ]);

        /** @var QuestionSetConvert $questionsetConverter */
        $questionsetConverter = app(QuestionSetConvert::class);
        list($id, $title, $machineName, $route, $resourceType) = $questionsetConverter->convert(
            H5PQuestionSet::$machineName,
            $questionSet,
            $resourceMetaObject
        );

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $id,
            'title' => $title,
            'license' => License::LICENSE_BY_NC,
        ]);
        $this->assertEquals(H5PQuestionSet::$machineName, $machineName);
        $this->assertEquals(route('h5p.edit', $id), $route);
        $this->assertEquals(Content::TYPE_H5P, $resourceType);
    }
}
