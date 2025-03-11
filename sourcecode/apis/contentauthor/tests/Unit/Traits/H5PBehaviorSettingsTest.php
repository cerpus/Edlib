<?php

namespace Tests\Unit\Traits;

use App\Libraries\DataObjects\BehaviorSettingsDataObject;
use App\Libraries\H5P\Packages\CoursePresentation;
use App\Libraries\H5P\Packages\MultiChoice;
use App\Traits\H5PBehaviorSettings;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class H5PBehaviorSettingsTest extends TestCase
{
    use H5PBehaviorSettings;

    #[Test]
    public function disableRetry_validStructures()
    {
        $behaviorSettings = BehaviorSettingsDataObject::create(false);

        $multichoice = new MultiChoice();
        $this->packageStructure = $multichoice->getPackageSemantics();
        $this->applyBehaviorSettings($behaviorSettings);
        $this->assertFalse($this->packageStructure->behaviour->enableRetry);

        $this->packageStructure = json_decode('{"taskDescription":"Drag the words into the correct boxes","overallFeedback":[{"from":0,"to":100}],"checkAnswer":"Check","tryAgain":"Retry","showSolution":"Show solution","dropZoneIndex":"Drop Zone @index.","empty":"Drop Zone @index is empty.","contains":"Drop Zone @index contains draggable @draggable.","draggableIndex":"Draggable @text. @index of @count draggables.","tipLabel":"Show tip","correctText":"Correct!","incorrectText":"Incorrect!","resetDropTitle":"Reset drop","resetDropDescription":"Are you sure you want to reset this drop zone?","grabbed":"Draggable is grabbed.","cancelledDragging":"Cancelled dragging.","correctAnswer":"Correct answer:","feedbackHeader":"Feedback","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"instantFeedback":false},"scoreBarLabel":"You got :num out of :total points","textField":"Dra *ordene* til *sin* rette *plass*"}');
        $this->applyBehaviorSettings($behaviorSettings);
        $this->assertFalse($this->packageStructure->behaviour->enableRetry);

        $behaviorSettings->enableRetry = true;
        $this->applyBehaviorSettings($behaviorSettings);
        $this->assertTrue($this->packageStructure->behaviour->enableRetry);
    }

    #[Test]
    public function disableRetry_invalidStructures()
    {
        $behaviorSettings = BehaviorSettingsDataObject::create(false);

        $this->packageStructure = (object) ["missingBehavior" => true];
        $this->applyBehaviorSettings($behaviorSettings);

        $this->assertJsonStringEqualsJsonString('{"missingBehavior":true}', $this->getPackageStructure(true));
    }

    #[Test]
    public function showSummary_validStructures()
    {
        $behaviorSettings = BehaviorSettingsDataObject::create();

        $coursePresentation = new CoursePresentation('{"presentation":{"slides":[{"elements":[],"keywords":[],"slideBackgroundSelector":{}}],"keywordListEnabled":true,"globalBackgroundSelector":{},"keywordListAlwaysShow":false,"keywordListAutoHide":false,"keywordListOpacity":90},"override":{"activeSurface":false,"hideSummarySlide":false,"enablePrintButton":false},"l10n":{"slide":"Slide","yourScore":"Your Score","maxScore":"Max Score","goodScore":"Congratulations! You got @percent correct!","okScore":"Nice effort! You got @percent correct!","badScore":"You got @percent correct.","Total":"Total","showSolutions":"Show solutions","retry":"Retry","title":"Title","author":"Author","lisence":"License","license":"License","exportAnswers":"Export text","copyright":"Rights of use","hideKeywords":"Hide keywords list","showKeywords":"Show keywords list","fullscreen":"Fullscreen","exitFullscreen":"Exit fullscreen","prevSlide":"Previous slide","nextSlide":"Next slide","currentSlide":"Current slide","lastSlide":"Last slide","solutionModeTitle":"Exit solution mode","solutionModeText":"Solution Mode","summaryMultipleTaskText":"Multiple tasks","scoreMessage":"You achieved:","shareFacebook":"Share on Facebook","shareTwitter":"Share on Twitter","summary":"Summary","solutionsButtonTitle":"Show comments","printTitle":"Print","printIngress":"How would you like to print this presentation?","printAllSlides":"Print all slides","printCurrentSlide":"Print current slide"}}');
        $packageStructure = json_decode($coursePresentation->applyBehaviorSettings($behaviorSettings));
        $this->assertFalse($packageStructure->override->hideSummarySlide);

        $behaviorSettings->showSummary = false;
        $packageStructure = json_decode($coursePresentation->applyBehaviorSettings($behaviorSettings));
        $this->assertTrue($packageStructure->override->hideSummarySlide);
    }
}
