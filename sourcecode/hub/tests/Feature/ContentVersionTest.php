<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

final class ContentVersionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param numeric-string $min
     * @param numeric-string $max
     */
    #[TestWith([false, '0.00', '0.00'])]
    #[TestWith([true, '1.00', '0.00'])]
    #[TestWith([true, '0.00', '1.00'])]
    public function testGivesScore(bool $expected, string $min, string $max): void
    {
        $v = new ContentVersion();
        $v->min_score = $min;
        $v->max_score = $max;

        $this->assertSame($expected, $v->givesScore());
    }

    public function testLaunchUrlIsExactVersionWhenSettingExistsInSession(): void
    {
        $version = ContentVersion::factory()
            ->withLaunchUrl('https://example.com/launch')
            ->create();

        $this->withSession([
            'lti' => [
                'ext_edlib3_return_exact_version' => '1',
            ],
        ]);

        $content = $version->content ?? $this->fail('Missing content');

        $this->assertSame(
            "https://hub-test.edlib.test/lti/content/{$content->id}/version/{$version->id}",
            $version->getExternalLaunchUrl(),
        );
    }

    public function testLaunchUrlIsLatestVersionOfContentByDefault(): void
    {
        $version = ContentVersion::factory()
            ->withLaunchUrl('https://example.com/launch')
            ->create();

        $content = $version->content ?? $this->fail('Missing content');

        $this->assertSame(
            "https://hub-test.edlib.test/lti/content/{$content->id}",
            $version->getExternalLaunchUrl(),
        );
    }
}
