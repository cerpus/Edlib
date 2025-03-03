<?php

namespace Tests\Integration\Libraries\H5P\Package;

use App\Libraries\H5P\Packages\MultiChoice;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MultiChoiceTest extends TestCase
{
    private array $parameters = [
        'onequestionwiththreeanswers' => '{"media":{"params":{}},"answers":[{"correct":true,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"Kong Harald"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"Kong Olav"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"Kong Haakon"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"Dronning Sonja"}],"UI":{"checkAnswerButton":"Check","showSolutionButton":"Show solution","tryAgainButton":"Retry","tipsLabel":"Show tip","scoreBarLabel":"Score","tipAvailable":"Tip available","feedbackAvailable":"Feedback available","readFeedback":"Read feedback","wrongAnswer":"Wrong answer","correctAnswer":"Correct answer","feedback":"You got @score of @total points","shouldCheck":"Should have been checked","shouldNotCheck":"Should not have been checked","noInput":"Please answer before viewing the solution"},"behaviour":{"enableRetry":true,"enableSolutionsButton":true,"type":"auto","singlePoint":false,"randomAnswers":true,"showSolutionsRequiresInput":true,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"autoCheck":false,"passPercentage":100,"showScorePoints":true},"confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"question":"Hvem er regent i Norge?"}',
        'oneQuestionWithThreeAnswersAndOneImage' => '{"media":{"params":{"contentName":"Image","file":{"path":"path/to/image#tmp","mime":"image/jpeg","copyright":{"license":"U"},"width":1244,"height":709},"alt":"Norge"},"library":"H5P.Image 1.0","subContentId":"96732b0d-98fb-4d42-a6a8-b9f13192529d"},"answers":[{"correct":true,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"Kong Harald"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"Kong Olav"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"Kong Haalpm"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"Dronning Sonja"}],"UI":{"checkAnswerButton":"Check","showSolutionButton":"Show solution","tryAgainButton":"Retry","tipsLabel":"Show tip","scoreBarLabel":"Score","tipAvailable":"Tip available","feedbackAvailable":"Feedback available","readFeedback":"Read feedback","wrongAnswer":"Wrong answer","correctAnswer":"Correct answer","feedback":"You got @score of @total points","shouldCheck":"Should have been checked","shouldNotCheck":"Should not have been checked","noInput":"Please answer before viewing the solution"},"behaviour":{"enableRetry":true,"enableSolutionsButton":true,"type":"auto","singlePoint":false,"randomAnswers":true,"showSolutionsRequiresInput":true,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"autoCheck":false,"passPercentage":100},"confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"question":"Hvem er regent i Norge?"}',
    ];

    #[Test]
    public function getPackageSemantics()
    {
        $structure = json_decode('{"media":{"params":{}},"answers":[],"UI":{"checkAnswerButton":"Check","showSolutionButton":"Show solution","tryAgainButton":"Retry","tipsLabel":"Show tip","scoreBarLabel":"Score","tipAvailable":"Tip available","feedbackAvailable":"Feedback available","readFeedback":"Read feedback","wrongAnswer":"Wrong answer","correctAnswer":"Correct answer","feedback":"You got @score of @total points","shouldCheck":"Should have been checked","shouldNotCheck":"Should not have been checked","noInput":"Please answer before viewing the solution"},"behaviour":{"enableRetry":true,"enableSolutionsButton":true,"type":"auto","singlePoint":false,"randomAnswers":true,"showSolutionsRequiresInput":true,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"autoCheck":false,"passPercentage":100,"showScorePoints":true},"confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"question":""}');

        $questionset = new MultiChoice();
        $generatedStructure = $questionset->getPackageSemantics();

        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
        $this->assertIsObject($generatedStructure);
        $this->assertObjectHasProperty("media", $generatedStructure);
        $this->assertObjectHasProperty("answers", $generatedStructure);
        $this->assertObjectHasProperty("UI", $generatedStructure);
        $this->assertObjectHasProperty("behaviour", $generatedStructure);
        $this->assertObjectHasProperty("confirmCheck", $generatedStructure);
        $this->assertObjectHasProperty("confirmRetry", $generatedStructure);
        $this->assertObjectHasProperty("question", $generatedStructure);
        $this->assertCount(7, array_keys((array) $generatedStructure));

        $this->assertEquals((array) $structure, (array) $generatedStructure);
    }

    #[Test]
    public function populateSemantics()
    {
        $multiChoice = new MultiChoice();
        $questionText = 'Hvem er regent i Norge?';
        $answers = [
            [
                'text' => 'Kong Harald',
                'correct' => true,
            ],
            [
                'text' => 'Kong Olav',
                'correct' => false,
            ],
            [
                'text' => 'Kong Haakon',
                'correct' => false,
            ],
            [
                'text' => 'Dronning Sonja',
                'correct' => false,
            ],
        ];

        $semantics = $multiChoice->populateSemanticsFromData([
            'text' => $questionText,
            'answers' => $answers,
        ]);

        $this->assertEquals($this->parameters['onequestionwiththreeanswers'], json_encode($semantics));
    }

    #[Test]
    public function populateSemanticsWithImage()
    {
        $this->markTestIncomplete();
        $multiChoice = new MultiChoice();
        $questionText = 'Hvem er regent i Norge?';
        $answers = [
            [
                'text' => 'Kong Harald',
                'correct' => true,
            ],
            [
                'text' => 'Kong Olav',
                'correct' => false,
            ],
            [
                'text' => 'Kong Haakon',
                'correct' => false,
            ],
            [
                'text' => 'Dronning Sonja',
                'correct' => false,
            ],
        ];

        $semantics = $multiChoice->populateSemanticsFromData([
            'text' => $questionText,
            'answers' => $answers,
            'image' => [],
        ]);

        $this->assertEquals($this->parameters['onequestionwiththreeanswers'], json_encode($semantics));
    }
}
