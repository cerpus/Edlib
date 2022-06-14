<?php

namespace App\Libraries\H5P\Packages;

class OpenEndedQuestion extends H5PBase
{
    public static string $machineName = 'H5P.OpenEndedQuestion';

    public function getElements(): array
    {
        return [
            'question' => $this->getQuestion(),
            'type' => self::class,
            "short_type" => "text",
            'answer' => $this->getAnswers(),
            "answers" => $this->getAnswersAsArray(),
            'composedComponent' => $this->isComposedComponent(),
        ];
    }

    private function getQuestion()
    {
        return $this->packageStructure->params->question;
    }

    public function validate(): bool
    {
        if (
            empty($this->packageStructure) ||
            empty($this->packageStructure->library) ||
            strpos($this->packageStructure->library, self::$machineName) !== 0 ||
            empty($this->packageStructure->params->question)
        ) {
            return false;
        }
        return true;
    }

    public function getAnswers($index = null)
    {
        return $this->answers;
    }

    public function getAnswersAsArray($index = null)
    {
        return [$this->getAnswers($index)];
    }


    public function getPackageAnswers($data)
    {
        return $data;
    }

    public function getPackageSemantics()
    {
        // TODO: Implement getPackageSemantics() method.
    }

    public function populateSemanticsFromData($data)
    {
        // TODO: Implement populateSemanticsFromData() method.
    }
}
