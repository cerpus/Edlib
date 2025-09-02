<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

final class EdlibLegacyTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['https://www.edlib.com/s/resources/a3dcbd28-bf37-4123-ac5e-ba2f72a8f420'], 'www.edlib.com')]
    #[TestWith(['https://www.h5p.ndla.no/s/resources/a3dcbd28-bf37-4123-ac5e-ba2f72a8f420'], 'www.h5p.ndla.no')]
    public function testRedirectsFromLegacyResourceUrls(string $url): void
    {
        $content = Content::factory()
            ->withPublishedVersion()
            ->edlib2Id('a3dcbd28-bf37-4123-ac5e-ba2f72a8f420')
            ->create();

        $this->get($url)
            ->assertRedirect('https://hub-test.edlib.test/content/' . $content->id . '/embed');
    }
}
