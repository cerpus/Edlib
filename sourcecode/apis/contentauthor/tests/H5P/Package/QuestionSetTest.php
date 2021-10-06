<?php

namespace Tests\H5P\Package;

use Tests\TestCase;
use App\Libraries\H5P\Packages\QuestionSet;

class QuestionSetTest extends TestCase
{

    /**
     * @test
     */
    public function getPackageSemantics()
    {
        $structure = json_decode('{"introPage":{"showIntroPage":false,"startButtonText":"Start Quiz","introduction":""},"progressType":"dots","passPercentage":50,"questions":[{"params":{}}],"texts":{"prevButton":"Previous question","nextButton":"Next question","finishButton":"Finish","textualProgress":"Question: @current of @total questions","jumpToQuestion":"Question %d of %total","questionLabel":"Question","readSpeakerProgress":"Question @current of @total","unansweredText":"Unanswered","answeredText":"Answered","currentQuestionText":"Current question"},"disableBackwardsNavigation":false,"randomQuestions":false,"endGame":{"showResultPage":true,"noResultMessage":"Finished","message":"Your result:","scoreString":"You got @score of @total points","successGreeting":"Congratulations!","successComment":"You did very well!","failGreeting":"You did not pass this time.","failComment":"Have another try!","solutionButtonText":"Show solution","retryButtonText":"Retry","finishButtonText":"Finish","showAnimations":false,"skippable":false,"skipButtonText":"Skip video"},"override":{}}');

        $questionset = new QuestionSet();
        $generatedStructure = $questionset->getPackageSemantics();

        $this->isJson($generatedStructure);
        $this->assertObjectHasAttribute("introPage", $generatedStructure);
        $this->assertObjectHasAttribute("progressType", $generatedStructure);
        $this->assertObjectHasAttribute("passPercentage", $generatedStructure);
        $this->assertObjectHasAttribute("questions", $generatedStructure);
        $this->assertObjectHasAttribute("texts", $generatedStructure);
        $this->assertObjectHasAttribute("disableBackwardsNavigation", $generatedStructure);
        $this->assertObjectHasAttribute("randomQuestions", $generatedStructure);
        $this->assertObjectHasAttribute("endGame", $generatedStructure);
        $this->assertObjectHasAttribute("override", $generatedStructure);
        $this->assertCount(9, array_keys((array)$generatedStructure));

        $this->assertEquals((array)$structure, (array)$generatedStructure);
    }
}
