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
    public $authId, $license, $title;

    protected $type = 'H5P.QuestionSet';

    /** @var \Illuminate\Support\Collection */
    private $questions;
    /** @var bool */
    private $sharing = false;

    /** @var int */
    private $score = 0;

    /** @var bool */
    public $published = true;

    public function __construct()
    {
        $this->questions = collect();
    }

    /**
     * @param MultiChoiceQuestion $question
     */
    public function addQuestion(MultiChoiceQuestion $question)
    {
        $this->questions->push($question);
        $this->updateScore($question);
    }

    /**
     * @param bool $sharing
     */
    public function setSharing(bool $sharing)
    {
        $this->sharing = $sharing;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @return bool
     */
    public function getSharing()
    {
        return $this->sharing;
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
