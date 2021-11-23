<?php

namespace Tests\H5P\API;

use App\H5PContent;
use App\H5PContentsUserData;
use App\Http\Middleware\Oauth2Authentication;
use App\Libraries\H5P\Packages\OpenEndedQuestion;
use App\Libraries\H5P\Reports\H5PPackage;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\db\TestH5PSeeder;
use Tests\TestCase;

use App\Http\Controllers\API\H5PReportController;
use Tests\Traits\WithFaker;

class H5PReportControllerTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    /**
     * @test
     */
    public function getQuestionsAndAnswers_noContext_thenFail()
    {
        $reportController = new H5PReportController();
        $request = new Request();

        $h5pPackage = $this->createMock(H5PPackage::class);

        $response = $reportController->questionAndAnswer($request, $h5pPackage);
        $this->assertEquals(400, $response->status());
        $this->assertCount(1, $response->original);
        $this->assertArrayHasKey("error", $response->original);
        $this->assertEquals("Missing contexts", $response->original['error']);
    }

    /**
     * @test
     */
    public function getQuestionsAndAnswers_oneInvalidContext_thenSuccess()
    {
        $reportController = new H5PReportController();
        $request = new Request([], [
            'contexts' => $this->faker->uuid
        ]);

        $h5pPackage = $this->createMock(H5PPackage::class);

        $response = $reportController->questionAndAnswer($request, $h5pPackage);
        $this->assertEquals(200, $response->status());
        $this->assertCount(0, $response->original);
    }

    /**
     * @test
     */
    public function getQuestionAndAnswers_oneValidContext_thenSuccess()
    {
        $reportController = new H5PReportController();
        $context = $this->faker->uuid;
        $request = new Request([], [
            'contexts' => $context
        ]);

        $h5pPackage = $this->createMock(H5PPackage::class);
        $h5pPackage->method('questionsAndAnswers')->willReturn([
            [
                'context' => $context,
                'elements' => [
                    'composedComponent' => false,
                    "type" => OpenEndedQuestion::class,
                    "short_type" => "text",
                    'question' => $this->faker->sentence,
                    'answer' => $this->faker->word,
                ],
            ]
        ]);

        $response = $reportController->questionAndAnswer($request, $h5pPackage);
        $this->assertEquals(200, $response->status());
        $this->assertCount(1, $response->original);
    }

    /**
     * @test
     */
    public function getQuestionsAndAnswersIntegrationTest_missingParameters_thenFail()
    {
        $this->withoutMiddleware();
        $response = $this->post("api/v1/questionsandanswers");
        $response->assertStatus(400);
        $response->assertExactJson(['error' => "Missing contexts"]);
    }

    /**
     * @test
     */
    public function getQuestionsAndAnswersIntegrationTest_invalidContext_thenSuccess()
    {
        $this->withoutMiddleware();
        $response = $this->post("api/v1/questionsandanswers", [
            'contexts' => 'NoValidContext'
        ]);
        $response->assertStatus(200);
        $response->assertExactJson([]);
    }

    /**
     * @test
     */
    public function getQuestionsAndAnswersIntegrationTest_validContext_thenSuccess()
    {
        $this->withoutMiddleware();
        $this->seed(TestH5PSeeder::class);

        $context = $this->faker->unique()->uuid;
        $userId = $this->faker->unique()->uuid;
        $answers = json_decode('{"questions":["Bar"],"progress":0,"finished":true,"version":1}');

        $content = H5PContent::factory()->create([
            'user_id' => $userId,
            'parameters' => '{"questionnaireElements":[{"library":{"params":{"placeholderText":"Start writing...","inputRows":"1","question":"Foo"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"e9fc6a28-3d63-4f3c-91b0-6a5e93ea440a"},"requiredField":false}],"successScreenOptions":{"enableSuccessScreen":true,"successScreenImage":{"params":{"contentName":"Image"},"library":"H5P.Image 1.0","subContentId":"6b3a6665-fa9d-43fb-82d7-30ba29ca5363"},"successMessage":"You have completed the questionnaire."},"uiElements":{"buttonLabels":{"prevLabel":"Back","continueLabel":"Continue","nextLabel":"Next","submitLabel":"Submit"},"accessibility":{"requiredTextExitLabel":"Close error message","progressBarText":"Question %current of %max"},"requiredMessage":"This question requires an answer","requiredText":"required","submitScreenTitle":"You successfully answered all of the questions","submitScreenSubtitle":"Click below to submit your answers"}}',
            'filtered' => '{"questionnaireElements":[{"library":{"params":{"placeholderText":"Start writing...","inputRows":"1","question":"Foo"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"e9fc6a28-3d63-4f3c-91b0-6a5e93ea440a"},"requiredField":false}],"successScreenOptions":{"enableSuccessScreen":true,"successScreenImage":{"params":{"contentName":"Image"},"library":"H5P.Image 1.0","subContentId":"6b3a6665-fa9d-43fb-82d7-30ba29ca5363"},"successMessage":"You have completed the questionnaire."},"uiElements":{"buttonLabels":{"prevLabel":"Back","continueLabel":"Continue","nextLabel":"Next","submitLabel":"Submit"},"accessibility":{"requiredTextExitLabel":"Close error message","progressBarText":"Question %current of %max"},"requiredMessage":"This question requires an answer","requiredText":"required","submitScreenTitle":"You successfully answered all of the questions","submitScreenSubtitle":"Click below to submit your answers"}}',
            'library_id' => 207
        ]);

        H5PContentsUserData::factory()->create([
            'data' => json_encode($answers),
            'context' => $context,
            'user_id' => $userId,
            'content_id' => $content->id
        ]);

        $response = $this->post("api/v1/questionsandanswers", [
            'contexts' => $context,
            'userId' => $userId
        ]);
        $response->assertStatus(200);
        $response->assertExactJson([
            [
                'context' => $context,
                'elements' => [
                    [
                        'text' => "Foo",
                        'answer' => "Bar",
                        "answers" => ["Bar"],
                        "type" => "text",
                    ]
                ]
            ]
        ]);
    }
}
