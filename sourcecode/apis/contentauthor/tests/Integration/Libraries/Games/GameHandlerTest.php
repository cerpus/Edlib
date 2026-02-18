<?php

namespace Tests\Integration\Libraries\Games;

use App\Exceptions\GameTypeNotFoundException;
use App\Gametype;
use App\Libraries\Games\GameHandler;
use App\Libraries\Games\Millionaire\Millionaire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GameHandlerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testGameTypeInstance_millionaire(): void
    {
        $this->assertInstanceOf(Millionaire::class, GameHandler::getGameTypeInstance('millionaire'));
        $this->assertInstanceOf(Millionaire::class, GameHandler::getGameTypeInstance(Millionaire::$machineName));
    }

    public function testGameTypeInstance_failes(): void
    {
        $this->expectException(GameTypeNotFoundException::class);

        GameHandler::getGameTypeInstance('unknown');
    }

    public function testGameTypeFromId_success(): void
    {
        $gt = Gametype::factory()->create([
            'name' => Millionaire::$machineName,
        ]);

        $this->assertInstanceOf(Millionaire::class, GameHandler::makeGameTypeFromId($gt->id));
    }

    public function testGameTypeFromId_fail(): void
    {
        $this->expectException(GameTypeNotFoundException::class);

        GameHandler::makeGameTypeFromId($this->faker->uuid);
    }
}
