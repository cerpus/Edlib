<?php

namespace App\Libraries\H5P\Packages;

use LogicException;

class QuestionSet extends H5PBase
{
    public static string $machineName = "H5P.QuestionSet";
    protected bool $canExtractAnswers = false;

    public static int $majorVersion = 1;
    public static int $minorVersion = 12;

    public function getElements(): array
    {
        // TODO: Implement getElements() method.
        throw new LogicException('This method is not implemented');
    }

    public function getAnswers($index = null)
    {
        // TODO: Implement getAnswers() method.
    }

    public function populateSemanticsFromData($data)
    {
        $semantics = $this->getPackageSemantics();
        $semantics->questions = $data->map(function ($question) {
            return (object) [
                'params' => $question['semantics'],
                'library' => $question['library'],
                'subContentId' => $question['subContentId'],
            ];
        })->toArray();
        return $semantics;
    }

    public function getPackageSemantics()
    {
        // TODO: Traverse the semantics.json in the actual directory for questionset
        return json_decode('{"introPage":{"showIntroPage":false,"startButtonText":"Start Quiz","introduction":""},"progressType":"dots","passPercentage":50,"questions":[{"params":{}}],"texts":{"prevButton":"Previous question","nextButton":"Next question","finishButton":"Finish","textualProgress":"Question: @current of @total questions","jumpToQuestion":"Question %d of %total","questionLabel":"Question","readSpeakerProgress":"Question @current of @total","unansweredText":"Unanswered","answeredText":"Answered","currentQuestionText":"Current question"},"disableBackwardsNavigation":false,"randomQuestions":false,"endGame":{"showResultPage":true,"noResultMessage":"Finished","message":"Your result:","scoreString":"You got @score of @total points","successGreeting":"Congratulations!","successComment":"You did very well!","failGreeting":"You did not pass this time.","failComment":"Have another try!","solutionButtonText":"Show solution","retryButtonText":"Retry","finishButtonText":"Finish","showAnimations":false,"skippable":false,"skipButtonText":"Skip video"},"override":{}}');
    }

    public function getPackageAnswers($data)
    {
        // TODO: Implement getPackageAnswers() method.
    }

    protected function alterRetryButton()
    {
        collect($this->packageStructure)
            ->filter(function ($values, $key) {
                return strtolower($key) === "override";
            })
            ->transform(function ($values) {
                if ($this->behaviorSettings->enableRetry === true) {
                    $values->retryButton = 'on';
                } elseif ($this->behaviorSettings->enableRetry === false) {
                    $values->retryButton = 'off';
                    $this->addCss('.h5p-container .questionset-results .result-text, .buttons .h5p-button.qs-retrybutton {display:none;}');
                }
                return $values;
            })
            ->toArray();
    }

    protected function alterShowSolutionButton()
    {
        collect($this->packageStructure)
            ->filter(function ($values, $key) {
                return strtolower($key) === "override";
            })
            ->transform(function ($values) {
                if ($this->behaviorSettings->showSolution === true) {
                    $values->showSolutionButton = 'on';
                } elseif ($this->behaviorSettings->showSolution === false) {
                    $values->showSolutionButton = 'off';
                    $this->addCss('.buttons .h5p-button.qs-solutionbutton {display:none;}');
                }
                return $values;
            })
            ->toArray();
    }
}
