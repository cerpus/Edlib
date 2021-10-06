<?php


namespace Tests\API;


use App\Events\ResourceSaved;
use App\H5PContent;
use App\H5PLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PublishResourceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $fakedEvents = [
        ResourceSaved::class
    ];

    public function testPublishResource()
    {
        $this->withoutMiddleware();

        $this->put('/api/v1/resources/1/publish')
            ->assertStatus(200);
    }

    public function testPublishResourceWithExistingResource()
    {
        Event::fake($this->fakedEvents);

        $this->withoutMiddleware();

        factory(H5PLibrary::class)->create(['id' => 1]);
        factory(H5PContent::class)->create(['id' => 1, 'library_id' => 1]);

        $this->put('/api/v1/resources/1/publish')
            ->assertStatus(200);

        $this->assertDatabaseHas('h5p_contents', ['id' => 1, 'is_published' => 1]);
    }
}
