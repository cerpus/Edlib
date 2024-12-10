<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentVersion;
use App\Models\LtiTool;
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

    /** @param array<mixed> $session */
    #[TestWith([[]])]
    #[TestWith([['lti' => ['ext_edlib3_return_exact_version' => '1']]])]
    public function testLaunchUrlIsUnproxiedWhenProxyingIsDisabled(array $session): void
    {
        $version = ContentVersion::factory()
            ->tool(LtiTool::factory()->proxyLaunch(false))
            ->withLaunchUrl('https://example.com/launch')
            ->create();

        $this->withSession($session);

        $this->assertSame(
            'https://example.com/launch',
            $version->getExternalLaunchUrl(),
        );
    }

    public function testLaunchUrlIsExactVersionWhenProxiedAndSettingExistsInSession(): void
    {
        $version = ContentVersion::factory()
            ->tool(LtiTool::factory()->proxyLaunch(true))
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

    public function testLaunchUrlIsLatestVersionOfContentByDefaultWhenProxyingIsEnabled(): void
    {
        $version = ContentVersion::factory()
            ->tool(LtiTool::factory()->proxyLaunch(true))
            ->withLaunchUrl('https://example.com/launch')
            ->create();

        $content = $version->content ?? $this->fail('Missing content');

        $this->assertSame(
            "https://hub-test.edlib.test/lti/content/{$content->id}",
            $version->getExternalLaunchUrl(),
        );
    }
}
