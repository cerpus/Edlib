<?php

namespace Tests\Unit\Models;

use App\Gametype;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GametypeTest extends TestCase
{
    use RefreshDatabase;
    
    public function testMostRecent()
    {
        $this->assertNull(Gametype::mostRecent());
        $this->assertNull(Gametype::mostRecent('CERPUS.GameA'));

        $gameA10 = factory(Gametype::class)->create(['title' => 'Game A 1.0']);
        $gameA11 = factory(Gametype::class)->create(['title' => 'Game A 1.1', 'minor_version' => 1]);
        $this->assertEquals($gameA11->id, Gametype::mostRecent('CERPUS.GameA')->id);

        $gameA20 = factory(Gametype::class)->create(['title' => 'Game A 2.0', 'major_version' => 2]);
        $this->assertEquals($gameA20->id, Gametype::mostRecent('CERPUS.GameA')->id);
        $this->assertEquals($gameA20->major_version, Gametype::mostRecent('CERPUS.GameA')->major_version);
        $this->assertEquals($gameA20->minor_version, Gametype::mostRecent('CERPUS.GameA')->minor_version);

        // Return correct gametype when more than one game has the same version number
        $gameB10 = factory(Gametype::class)->create(['title' => 'Game B 1.0', 'name' => 'CERPUS.GameB']);
        $this->assertEquals($gameB10->id, Gametype::mostRecent('CERPUS.GameB')->id);

        $this->assertCount(4, Gametype::all());

        // Return null if the game type does not exist
        $this->assertNull(Gametype::mostRecent('CERPUS.DOES_NOT_EXIST'));
    }
}
