<?php

declare(strict_types=1);

namespace Feature;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

final class EdlibLegacyTest extends TestCase
{
    use RefreshDatabase;

    public function testRedirectsFromLegacyResourceUrls(): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->tag('edlib2_id:a3dcbd28-bf37-4123-ac5e-ba2f72a8f420')
            ->create();

        $this->get('https://www.edlib.com/s/resources/a3dcbd28-bf37-4123-ac5e-ba2f72a8f420')
            ->assertRedirect('https://hub-test.edlib.test/content/' . $content->id . '/embed');
    }

    #[TestWith(['https://core.cerpus-course.com/lti/launch/eda9e9c4-d64a-4cb5-9f4a-c50d0f0e5f17'], 'cerpus-course.com')]
    #[TestWith(['https://api.edlib.com/lti/v2/lti-links/eda9e9c4-d64a-4cb5-9f4a-c50d0f0e5f17'], 'api.edlib.com')]
    public function testRedirectsFromLegacyLtiLaunch(string $url): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->tag('edlib2_usage_id:eda9e9c4-d64a-4cb5-9f4a-c50d0f0e5f17')
            ->create();

        $this->post($url)
            ->assertStatus(307)
            ->assertRedirect('https://hub-test.edlib.test/content/' . $content->id . '/embed');
    }
}
