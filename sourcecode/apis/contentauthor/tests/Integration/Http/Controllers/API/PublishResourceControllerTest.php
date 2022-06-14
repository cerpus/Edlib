<?php


namespace Tests\Integration\Http\Controllers\API;


use App\Events\ResourceSaved;
use App\H5PContent;
use App\H5PLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PublishResourceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected array $fakedEvents = [
        ResourceSaved::class
    ];

    public function testPublishResource()
    {
        $this->withoutMiddleware();
        config([
            'feature' => [
                'enableUserPublish' => 'true'
            ]
        ]);

        $this->put('/api/v1/resources/1/publish')
            ->assertStatus(200);
    }

    public function testPublishResourceWithExistingResource()
    {
        Event::fake($this->fakedEvents);

        $this->withoutMiddleware();
        config([
            'feature' => [
                'enableUserPublish' => 'true'
            ]
        ]);

        H5PLibrary::factory()->create(['id' => 1]);
        H5PContent::factory()->create(['id' => 1, 'library_id' => 1]);

        $this->put('/api/v1/resources/1/publish')
            ->assertStatus(200);

        $this->assertDatabaseHas('h5p_contents', ['id' => 1, 'is_published' => 1]);
    }
}
