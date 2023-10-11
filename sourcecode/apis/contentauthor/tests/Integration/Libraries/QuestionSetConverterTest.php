<?php

namespace Tests\Integration\Libraries;

use App\Events\GameWasSaved;
use App\Events\H5PWasSaved;
use App\Gametype;
use App\H5PContent;
use App\H5PLibrary;
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

        $questionSet = QuestionSet::factory()->create([
            'is_published' => false,
        ]);
        $gameType = Gametype::factory()->create([
            'name' => Millionaire::$machineName,
        ]);
        $resourceMetaObject = new ResourceMetadataDataObject(
            license: License::LICENSE_BY_NC,
            share: 'share',
            tags: ['List', 'of', 'tags'],
        );

        $questionsetConverter = app(QuestionSetConvert::class);
        $game = $questionsetConverter->convert(
            Millionaire::$machineName,
            $questionSet,
            $resourceMetaObject
        );

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'title' => $questionSet->title,
            'license' => License::LICENSE_BY_NC,
            'gametype' => $gameType->id,
        ]);
    }

    public function testCreateH5PQuestionSet(): void
    {
        $this->expectsEvents([H5PWasSaved::class]);

        $h5peditorMock = $this->createMock(H5peditor::class);
        app()->instance(H5peditor::class, $h5peditorMock);
        $h5peditorMock
            ->expects($this->once())
            ->method('processParameters');

        $questionSet = QuestionSet::factory()->create([
            'is_published' => false,
            'license' => License::LICENSE_BY_ND,
        ]);
        $question = QuestionSetQuestion::factory()->create([
            'question_set_id' => $questionSet->id,
        ]);
        QuestionSetQuestionAnswer::factory()->count(4)->create([
            'question_id' => $question->id,
        ]);
        $resourceMetaObject = new ResourceMetadataDataObject(
            license: License::LICENSE_BY_NC,
            share: 'share',
            tags: ['List', 'of', 'tags'],
        );

        H5PLibrary::factory([
            'name' => 'H5P.QuestionSet',
            'major_version' => 1,
            'minor_version' => 12,
        ])->create();

        $questionsetConverter = app(QuestionSetConvert::class);
        $h5p = $questionsetConverter->convert(
            H5PQuestionSet::$machineName,
            $questionSet,
            $resourceMetaObject
        );

        $this->assertInstanceOf(H5PContent::class, $h5p);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $h5p->id,
            'title' => $questionSet->title,
            'license' => License::LICENSE_BY_NC,
        ]);
        $this->assertSame('H5P.QuestionSet', $h5p->getMachineName());
    }
}
