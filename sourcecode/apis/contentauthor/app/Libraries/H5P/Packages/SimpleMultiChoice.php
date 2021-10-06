<?php

namespace App\Libraries\H5P\Packages;


class SimpleMultiChoice extends H5PBase
{

    public static $machineName = "H5P.SimpleMultiChoice";

    public function getElements(): array
    {
        return [
            'question' => $this->getQuestion(),
            'type' => self::class,
            "short_type" => "options",
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
        if (empty($this->packageStructure) ||
            empty($this->packageStructure->library) ||
            strpos($this->packageStructure->library, self::$machineName) !== 0 ||
            empty($this->packageStructure->params->question)) {
            return false;
        }
        return true;
    }

    public function getAnswers($index = null)
    {
        return implode(", ", $this->getAnswersAsArray($index));
    }

    public function getAnswersAsArray($index = null) : array
    {
        $alternatives = $this->getAlternatives();

        if (!is_null($this->answers) && !empty($alternatives)) {
            $answerArray = explode('[,]', $this->answers);
            $answers = collect($alternatives)
                ->reject(function ($item, $index) use ($answerArray) {
                    return !in_array($index, $answerArray);
                })
                ->map(function ($answer) {
                    return $answer->text;
                })
                ->toArray();

            return $answers;
        }

        return $this->answers ?? [];
    }


    public function getPackageAnswers($data)
    {
        return $data;
    }

    private function getAlternatives()
    {
        return !empty($this->packageStructure->params->alternatives) ? $this->packageStructure->params->alternatives : [];
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
