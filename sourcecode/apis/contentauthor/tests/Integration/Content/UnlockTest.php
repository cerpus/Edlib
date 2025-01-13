<?php

namespace Tests\Integration\Content;

use App\ContentLock;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class UnlockTest extends TestCase
{
    use RefreshDatabase;

    public function testUnlockSuccess()
    {
        $user = User::factory()->make();
        $lockStatus = ContentLock::factory()->create([
            'auth_id' => $user->auth_id,
            'updated_at' => Carbon::now()->subMinutes(45)->subSeconds(30),
        ]);


        $this->withSession(['authId' => $user->auth_id])
            ->assertCount(1, ContentLock::all());

        $this->get(route('lock.unlock', $lockStatus->content_id))
            ->assertJson([
                'status' => 'OK',
                'code' => 200,
            ]);
        $this->assertCount(0, ContentLock::all());
    }

    public function testUnlockFailBecauseNotOwner()
    {
        $user = User::factory()->make();
        $lockStatus = ContentLock::factory()->create([
            'auth_id' => $user->auth_id,
            'updated_at' => Carbon::now()->subMinutes(45)->subSeconds(30),
        ]);

        $eve = User::factory()->make();

        $this->withSession(['authId' => $eve->auth_id])
            ->get(route('lock.unlock', $lockStatus->content_id))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJsonStructure([
                'code',
                'status',
            ])
            ->assertJson([
                'code' => Response::HTTP_FORBIDDEN,
                'status' => 'fail',
            ]);

        $this->assertCount(1, ContentLock::all());
    }

    public function testUnlockNotFound()
    {
        $user = User::factory()->make();

        $this->assertCount(0, ContentLock::all());
        $this->withSession(['authId' => $user->auth_id])
            ->get(route('lock.unlock', 1234))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'code',
                'status',
            ])
            ->assertJson([
                'code' => Response::HTTP_OK,
                'status' => 'not found',
            ]);
    }
}
