<?php

namespace App\Libraries\Games\Contracts;

use App\Game;
use Illuminate\Http\Request;
use Illuminate\View\View;

interface GameTypeContract
{
    public function getGameType();

    public function createGameSettings(array $parameters, bool $asObject = false): object|string;

    public function view(Game $game, $context): View;

    public function create(Request $request): View;

    public function edit(Game $game, Request $request): View;

    public function alterGameSettings($gameSettings);

    public static function customValidation($dataToBeValidated);

    public function convertLanguageCode($languageCode);

    public function getMaxScore();
}
