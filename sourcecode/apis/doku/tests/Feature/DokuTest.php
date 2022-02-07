<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Doku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class DokuTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testGet(): void
    {
        /** @var Doku $doku */
        $doku = Doku::factory()->create();

        $this
            ->getJson("/doku/v1/dokus/{$doku->id}")
            ->assertOk()
            ->assertJson([
                'id' => $doku->id,
                'title' => $doku->title,
                'creator_id' => $doku->creator_id,
                'data' => $doku->data,
                'public' => $doku->public,
                'draft' => $doku->draft,
                'created_at' => $doku->created_at->toJSON(),
                'updated_at' => $doku->updated_at->toJSON(),
            ]);
    }

    public function testGetPaginated(): void
    {
        Doku::factory()->count(120)->create();

        $response = $this
            ->getJson('/doku/v1/dokus')
            ->assertOk()
            ->assertJsonCount(100, 'data');

        $this->assertIsString($response->json('next_page_url'));

        $this->getJson($response->json('next_page_url'))
            ->assertOk()
            ->assertJsonCount(20, 'data');
    }

    public function testCreate(): void
    {
        $data = [
            'title' => $this->faker->sentence,
            'data' => [
                $this->faker->word => $this->faker->word,
            ],
        ];

        $response = $this
            ->postJson('/doku/v1/dokus', $data)
            ->assertCreated()
            ->assertJson([
                'title' => $data['title'],
                'data' => $data['data'],
                'public' => false,
                'draft' => true,
            ]);

        $id = $response->json('id');

        $this->assertTrue(Doku::whereId($id)->exists());
    }

    public function testUpdate(): void
    {
        $doku = Doku::factory()->create();

        $data = [
            'title' => $this->faker->sentence,
            'data' => [
                $this->faker->word => $this->faker->word,
            ],
        ];

        $this
            ->patchJson('/doku/v1/dokus/' . $doku->id, $data)
            ->assertOk()
            ->assertJson([
                'id' => $doku->id,
                'title' => $data['title'],
                'data' => $data['data'],
                'public' => $doku->public,
                'draft' => $doku->draft,
            ]);

        $doku = $doku->fresh();
        $this->assertSame($data['title'], $doku->title);
        $this->assertSame($data['data'], $doku->data);
    }

    public function testPublish(): void
    {
        $doku = Doku::factory()->create();

        $this->postJson('/doku/v1/dokus/' . $doku->id . '/publish')
            ->assertOk()
            ->assertJson([
                'public' => true,
                'draft' => false,
            ]);
    }

    public function testUnpublish(): void
    {
        $doku = Doku::factory()->create();

        $this->postJson('/doku/v1/dokus/' . $doku->id . '/unpublish')
            ->assertOk()
            ->assertJson([
                'draft' => $doku->draft,
                'public' => false,
            ]);
    }
}
