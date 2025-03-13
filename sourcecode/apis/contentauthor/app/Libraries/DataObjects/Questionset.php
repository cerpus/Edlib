<?php

namespace App\Libraries\DataObjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static Questionset create($attributes = null)
 */
class Questionset
{
    use CreateTrait;

    /** @var string $authId */
    /** @var string $licence */
    /** @var string $title */
    public $authId;
    public $license;
    public $title;

    protected $type = 'H5P.QuestionSet';

    /** @var \Illuminate\Support\Collection */
    private $questions;

    /** @var int */
    private $score = 0;

    /** @var bool */
    public $published = true;

    public function __construct()
    {
        $this->questions = collect();
    }


    public function addQuestion(MultiChoiceQuestion $question)
    {
        $this->questions->push($question);
        $this->updateScore($question);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    public function getScore()
    {
        return $this->score;
    }

    private function updateScore(MultiChoiceQuestion $question)
    {
        $this->score += $question->getAnswers()
            ->filter(function ($answer) {
                return $answer->correct;
            })
            ->count();
    }
}
