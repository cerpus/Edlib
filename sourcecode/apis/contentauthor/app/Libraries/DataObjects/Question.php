<?php

namespace App\Libraries\DataObjects;

use Illuminate\Support\Collection;

abstract class Question
{
    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Collection
     */
    protected $answers;

    public function __construct()
    {
        $this->answers = collect();
    }

    public function addAnswer(Answer $answer)
    {
        $this->answers->push($answer);
    }

    public function addAnswers(Collection $answers)
    {
        $that = $this;
        $answers->each(function($answer) use ($that) {
            $that->addAnswer($answer);
        });
    }

    /**
     * @return Collection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }
}
