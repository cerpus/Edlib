<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
