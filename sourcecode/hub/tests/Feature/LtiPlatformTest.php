<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\LaunchLti;
use App\Lti\LtiLaunchBuilder;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

/**
 * Tests suitability as an LTI platform, i.e. a consumer of LTI tools.
 */
final class LtiPlatformTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testBuildingItemSelectionLaunchDispatchesEvent(): void
    {
        Event::fake();

        $tool = LtiTool::factory()->create();

        $builder = $this->app->make(LtiLaunchBuilder::class);

        Event::assertNotDispatched(LaunchLti::class);

        $builder->toItemSelectionLaunch(
            $tool,
            'https://example.com/',
            'https://return.example.com/',
        );

        Event::assertDispatched(LaunchLti::class);
    }

    public function testItemSelectionLaunchContainsPlatformDetails(): void
    {
        $tool = LtiTool::factory()->create();

        $builder = $this->app->make(LtiLaunchBuilder::class);

        $request = $builder->toItemSelectionLaunch(
            $tool,
            'https://example.com/',
            'https://return.example.com/',
        )->getRequest();

        $this->assertSame('Edlib', $request->get('tool_consumer_info_product_family_code'));
        $this->assertSame('3', $request->get('tool_consumer_info_version'));
        $this->assertSame('EdlibTest', $request->get('tool_consumer_instance_name'));
        $this->assertSame('https://hub-test.edlib.test', $request->get('tool_consumer_instance_url'));
    }

    public function testItemSelectionLaunchContainsLocale(): void
    {
        $this->app->setLocale('fr');

        $tool = LtiTool::factory()->create();

        $request = $this->app->make(LtiLaunchBuilder::class)
            ->toItemSelectionLaunch(
                $tool,
                'https://example.com/',
                'https://return.example.com/',
            )
            ->getRequest();

        $this->assertSame('fr', $request->get('launch_presentation_locale'));
    }

    public function testLaunchesFromAnonymousUsersDoNotContainUserDetails(): void
    {
        $tool = LtiTool::factory()->create([
            'send_name' => true,
            'send_email' => true,
        ]);

        $request = $this->app->make(LtiLaunchBuilder::class)
            ->toItemSelectionLaunch(
                $tool,
                'https://example.com/',
                'https://return.example.com/',
            )
            ->getRequest();

        $this->assertFalse($request->has('lis_person_name_full'));
        $this->assertFalse($request->has('lis_person_name_family'));
        $this->assertFalse($request->has('lis_person_name_given'));
        $this->assertFalse($request->has('lis_person_contact_email_primary'));
        $this->assertFalse($request->has('user_id'));
    }

    public function testLaunchesFromLoggedInUsersContainUserDetails(): void
    {
        $user = User::factory()->create([
            'name' => 'Chandler Bing',
            'email' => 'chandler@bing.com',
        ]);
        $this->actingAs($user);

        $tool = LtiTool::factory()->create([
            'send_name' => true,
            'send_email' => true,
        ]);

        $request = $this->app->make(LtiLaunchBuilder::class)
            ->toItemSelectionLaunch(
                $tool,
                'https://example.com/',
                'https://return.example.com/',
            )
            ->getRequest();

        $this->assertSame('Chandler Bing', $request->get('lis_person_name_full'));
        $this->assertSame('Bing', $request->get('lis_person_name_family'));
        $this->assertSame('Chandler', $request->get('lis_person_name_given'));
        $this->assertSame('chandler@bing.com', $request->get('lis_person_contact_email_primary'));
        $this->assertSame($user->id, $request->get('user_id'));
    }

    public function testLaunchDoesNotContainUnverifiedEmail(): void
    {
        $user = User::factory()
            ->name('Chanandler Bong')
            ->withEmail('chandler@bing.com', verified: false)
            ->create();

        $this->actingAs($user);

        $tool = LtiTool::factory()->create([
            'send_name' => true,
            'send_email' => true,
        ]);

        $request = $this->app->make(LtiLaunchBuilder::class)
            ->toItemSelectionLaunch(
                $tool,
                'https://example.com/',
                'https://return.example.com/',
            )
            ->getRequest();

        $this->assertSame('Chanandler Bong', $request->get('lis_person_name_full'));
        $this->assertSame('Bong', $request->get('lis_person_name_family'));
        $this->assertSame('Chanandler', $request->get('lis_person_name_given'));
        $this->assertFalse($request->has('lis_person_contact_email_primary'));
        $this->assertSame($user->id, $request->get('user_id'));
    }

    #[TestWith(['1', true])]
    #[TestWith(['0', false])]
    public function testClaimsContainPublishedFlagWhenEditing(
        string $flagValue,
        bool $published,
    ): void {
        $version = ContentVersion::factory()->published($published)->create();

        $request = $this->app->make(LtiLaunchBuilder::class)
            ->toItemSelectionLaunch(
                tool: $version->tool ?? $this->fail(),
                url: $this->faker->url,
                itemReturnUrl: $this->faker->url,
                version: $version,
            )
            ->getRequest();

        $this->assertSame($flagValue, $request->get('ext_edlib3_published'));
    }

    #[TestWith(['1', true])]
    #[TestWith(['0', false])]
    public function testClaimsContainSharedFlagWhenEditing(
        string $flagValue,
        bool $shared,
    ): void {
        $content = Content::factory()
            ->withVersion(ContentVersion::factory()->published())
            ->shared($shared)
            ->create();

        $request = $this->app->make(LtiLaunchBuilder::class)
            ->toItemSelectionLaunch(
                tool: $content->latestPublishedVersion->tool ?? $this->fail(),
                url: $this->faker->url,
                itemReturnUrl: $this->faker->url,
                version: $content->latestPublishedVersion ?? $this->fail(),
            )
            ->getRequest();

        $this->assertSame($flagValue, $request->get('ext_edlib3_shared'));
    }

    #[TestWith(['1', true])]
    #[TestWith(['0', false])]
    public function testClaimsContainDefaultPublishedFlagWhenCreating(
        string $flagValue,
        bool $defaultPublished,
    ): void {
        $tool = LtiTool::factory()->defaultPublished($defaultPublished)->create();

        $request = $this->app->make(LtiLaunchBuilder::class)
            ->toItemSelectionLaunch(
                tool: $tool,
                url: $this->faker->url,
                itemReturnUrl: $this->faker->url,
            )
            ->getRequest();

        $this->assertSame($flagValue, $request->get('ext_edlib3_published'));
    }

    #[TestWith(['1', true])]
    #[TestWith(['0', false])]
    public function testClaimsContainDefaultSharedFlagWhenCreating(
        string $flagValue,
        bool $defaultShared,
    ): void {
        $tool = LtiTool::factory()->defaultShared($defaultShared)->create();

        $request = $this->app->make(LtiLaunchBuilder::class)
            ->toItemSelectionLaunch(
                tool: $tool,
                url: $this->faker->url,
                itemReturnUrl: $this->faker->url,
            )
            ->getRequest();

        $this->assertSame($flagValue, $request->get('ext_edlib3_shared'));
    }
}
