<?php

declare(strict_types=1);

namespace Tests\Feature\NdlaLegacy;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewResourceTest extends TestCase
{
    use RefreshDatabase;

    public function testEndpointIsUnavailableWhenLegacyDisabled(): void
    {
        $this->app->make('config')->set('ndla-legacy.domain', null);

        Content::factory()
            ->edlib2UsageId('8dc67e6b-653f-46e4-8ab0-16e61bbfca43')
            ->withPublishedVersion()
            ->create();

        $this->get('https://hub-test-ndla-legacy.edlib.test/resource/8dc67e6b-653f-46e4-8ab0-16e61bbfca43')
            ->assertNotFound();
    }

    public function testRedirectsFromLegacyResourceUrl(): void
    {
        $content = Content::factory()
            ->edlib2UsageId('8dc67e6b-653f-46e4-8ab0-16e61bbfca43')
            ->withPublishedVersion()
            ->create();

        $this->get('https://hub-test-ndla-legacy.edlib.test/resource/8dc67e6b-653f-46e4-8ab0-16e61bbfca43')
            ->assertRedirect('https://hub-test.edlib.test/content/' . $content->id . '/embed');
    }

    public function testRedirectIncludesLocale(): void
    {
        $content = Content::factory()
            ->edlib2UsageId('8dc67e6b-653f-46e4-8ab0-16e61bbfca43')
            ->withPublishedVersion()
            ->create();

        $this->get('https://hub-test-ndla-legacy.edlib.test/resource/8dc67e6b-653f-46e4-8ab0-16e61bbfca43?locale=nb-NO')
            ->assertRedirect('https://hub-test.edlib.test/content/' . $content->id . '/embed?locale=nb-NO');
    }
}
