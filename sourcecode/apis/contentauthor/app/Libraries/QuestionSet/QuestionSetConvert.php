<?php

namespace App\Libraries\QuestionSet;

use App\Content;
use App\Game;
use App\Libraries\DataObjects\ResourceMetadataDataObject;
use App\Libraries\Games\GameHandler;
use App\Libraries\Games\Millionaire\Millionaire;
use App\QuestionSet;

readonly class QuestionSetConvert
{
    public function __construct(
        private GameHandler $gameHandler,
    ) {}

    public function convert(string $convertTo, QuestionSet|array $questionSet, ResourceMetadataDataObject $metadata): Content
    {
        return match ($convertTo) {
            Millionaire::$machineName => $this->createMillionaireGame($questionSet, $metadata),
            default => throw new \InvalidArgumentException("Presentation '$convertTo' is not currently supported'"),
        };
    }

    public function createMillionaireGame(QuestionSet|array $questionSet, ResourceMetadataDataObject $metaData): Game
    {
        return $this->gameHandler->store([
            'title' => $questionSet['title'],
            'cards' => is_array($questionSet) ? $questionSet['cards'] : $questionSet,
            'license' => $metaData->license,
            'share' => $metaData->share,
            'authId' => $questionSet['owner'],
            'tags' => $metaData->tags,
            'is_published' => $questionSet['is_published'],
            'language_code' => $questionSet['language_code'],
        ], new Millionaire());
    }
}
