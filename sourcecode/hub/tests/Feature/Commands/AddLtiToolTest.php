<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Enums\LtiToolEditMode;
use App\Enums\LtiVersion;
use App\Models\LtiTool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddLtiToolTest extends TestCase
{
    use RefreshDatabase;

    public function testAddsLtiTool(): void
    {
        $this->command('edlib:add-lti-tool', [
            'name' => 'Content Author',
            'url' => 'https://ca.edlib.test/lti-content/create',
            '--slug' => 'sluggish-tool',
            '--send-name' => true,
            '--send-email' => true,
            '--edlib-editable' => true,
        ])
            ->expectsQuestion('Key', 'h5p')
            ->expectsQuestion('Secret', 'secret2')
            ->assertSuccessful();

        $tool = LtiTool::where('name', 'Content Author')->firstOrFail();

        $this->assertSame('https://ca.edlib.test/lti-content/create', $tool->creator_launch_url);
        $this->assertTrue($tool->send_name);
        $this->assertTrue($tool->send_email);
        $this->assertSame('h5p', $tool->consumer_key);
        $this->assertSame('secret2', $tool->consumer_secret);
        $this->assertSame(LtiToolEditMode::DeepLinkingRequestToContentUrl, $tool->edit_mode);
        $this->assertSame(LtiVersion::Lti1_1, $tool->lti_version);
        $this->assertSame('sluggish-tool', $tool->slug);
    }
}
