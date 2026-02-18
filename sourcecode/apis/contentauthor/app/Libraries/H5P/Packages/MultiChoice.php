<?php

namespace App\Libraries\H5P\Packages;

use App\Libraries\H5P\Helper\H5PPackageProvider;
use LogicException;

class MultiChoice extends H5PBase
{
    public static string $machineName = "H5P.MultiChoice";
    public static int $majorVersion = 1;
    public static int $minorVersion = 9;

    protected bool $canExtractAnswers = false;

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
        if (!empty($data['text'])) {
            $semantics->question = nl2br($data['text']);
        }

        if (!empty($data['answers'])) {
            $semantics->answers = collect($data['answers'])
                ->map(function ($answer) {
                    $answer = (array) $answer;
                    return [
                        'correct' => $answer['correct'],
                        'tipsAndFeedback' => (object) [
                            'tip' => "",
                            'chosenFeedback' => "",
                            'notChosenFeedback' => "",
                        ],
                        'text' => nl2br($answer['text']),
                    ];
                })->toArray();
        }
        return $semantics;
    }

    public function getPackageSemantics()
    {
        // TODO: Traverse the semantics.json in the actual directory for multiplechoice
        return json_decode('{"media":{"params":{}},"answers":[],"UI":{"checkAnswerButton":"Check","showSolutionButton":"Show solution","tryAgainButton":"Retry","tipsLabel":"Show tip","scoreBarLabel":"Score","tipAvailable":"Tip available","feedbackAvailable":"Feedback available","readFeedback":"Read feedback","wrongAnswer":"Wrong answer","correctAnswer":"Correct answer","feedback":"You got @score of @total points","shouldCheck":"Should have been checked","shouldNotCheck":"Should not have been checked","noInput":"Please answer before viewing the solution"},"behaviour":{"enableRetry":true,"enableSolutionsButton":true,"type":"auto","singlePoint":false,"randomAnswers":true,"showSolutionsRequiresInput":true,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"autoCheck":false,"passPercentage":100,"showScorePoints":true},"confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"question":""}');
    }


    public function getPackageAnswers($data)
    {
        // TODO: Implement getPackageAnswers() method.
    }

    private function getMedia()
    {
        return $this->packageStructure->media ?? [];
    }

    /**
     * @return bool
     * @throws \App\Exceptions\UnknownH5PPackageException
     * @throws \Exception
     */
    public function alterSource($sourceFile, array $newSource)
    {
        $media = $this->getMedia();

        if (empty($media)) {
            return true;
        }

        if (!empty($media->type)) {
            $package = H5PPackageProvider::make($media->type->library, $media->type->params);
            if ($package->alterSource($sourceFile, $newSource) !== true) {
                throw new \Exception("Could not update source");
            }
            $media->type->params = $package->getPackageStructure();
        }

        return true;
    }
}
