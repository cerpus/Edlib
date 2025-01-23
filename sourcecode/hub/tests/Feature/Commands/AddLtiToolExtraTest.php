<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Models\LtiTool;
use App\Models\LtiToolExtra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddLtiToolExtraTest extends TestCase
{
    use RefreshDatabase;

    public function testAddsLtiTool(): void
    {
        $tool = LtiTool::factory()->slug('content-author')->create();

        $this->command('edlib:add-lti-tool-extra', [
            'parent' => 'content-author',
            'name' => 'CA admin',
            'url' => 'https://ca.edlib.test/lti/admin',
            '--slug' => 'ca-admin',
            '--admin' => true,
        ])->assertSuccessful();

        $extra = LtiToolExtra::where('slug', 'ca-admin')->firstOrFail();
        assert($extra instanceof LtiToolExtra);

        $this->assertTrue($extra->tool?->is($tool));
        $this->assertSame('CA admin', $extra->name);
        $this->assertSame('https://ca.edlib.test/lti/admin', $extra->lti_launch_url);
        $this->assertSame('ca-admin', $extra->slug);
        $this->assertTrue($extra->admin);
    }
}
