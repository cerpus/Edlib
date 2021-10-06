<?php

namespace App\Libraries\Games\Contracts;


use App\Game;
use Illuminate\Http\Request;

interface GameTypeContract
{
    public function getGameType();

    public function createGameSettings($parameters, $asObject = false);


    public function view(Game $game, $context, $preview);

    public function edit(Game $game, Request $request);

    public function alterGameSettings($gameSettings);

    public static function customValidation($dataToBeValidated);

    public function convertLanguageCode($languageCode);

    public function getMaxScore();
}
