<?php

namespace Tests\Integration\Libraries\H5P\Package;

use App\Libraries\H5P\Packages\QuestionSet;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuestionSetTest extends TestCase
{
    #[Test]
    public function getPackageSemantics()
    {
        $structure = json_decode('{"introPage":{"showIntroPage":false,"startButtonText":"Start Quiz","introduction":""},"progressType":"dots","passPercentage":50,"questions":[{"params":{}}],"texts":{"prevButton":"Previous question","nextButton":"Next question","finishButton":"Finish","textualProgress":"Question: @current of @total questions","jumpToQuestion":"Question %d of %total","questionLabel":"Question","readSpeakerProgress":"Question @current of @total","unansweredText":"Unanswered","answeredText":"Answered","currentQuestionText":"Current question"},"disableBackwardsNavigation":false,"randomQuestions":false,"endGame":{"showResultPage":true,"noResultMessage":"Finished","message":"Your result:","scoreString":"You got @score of @total points","successGreeting":"Congratulations!","successComment":"You did very well!","failGreeting":"You did not pass this time.","failComment":"Have another try!","solutionButtonText":"Show solution","retryButtonText":"Retry","finishButtonText":"Finish","showAnimations":false,"skippable":false,"skipButtonText":"Skip video"},"override":{}}');

        $questionset = new QuestionSet();
        $generatedStructure = $questionset->getPackageSemantics();

        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
        $this->assertIsObject($generatedStructure);
        $this->assertObjectHasProperty("introPage", $generatedStructure);
        $this->assertObjectHasProperty("progressType", $generatedStructure);
        $this->assertObjectHasProperty("passPercentage", $generatedStructure);
        $this->assertObjectHasProperty("questions", $generatedStructure);
        $this->assertObjectHasProperty("texts", $generatedStructure);
        $this->assertObjectHasProperty("disableBackwardsNavigation", $generatedStructure);
        $this->assertObjectHasProperty("randomQuestions", $generatedStructure);
        $this->assertObjectHasProperty("endGame", $generatedStructure);
        $this->assertObjectHasProperty("override", $generatedStructure);
        $this->assertCount(9, array_keys((array) $generatedStructure));

        $this->assertEquals((array) $structure, (array) $generatedStructure);
    }
}
