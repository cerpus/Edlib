<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\LtiTool;
use App\Models\LtiToolExtra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminsCanViewTheAdminHome(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function testCannotShowAdminHomeWhenLoggedOut(): void
    {
        $this->get('/admin')
            ->assertForbidden();
    }

    public function testCannotShowAdminHomeWhenNotAdmin(): void
    {
        $login = User::factory()->create();

        $this->actingAs($login)
            ->get('/admin')
            ->assertForbidden();
    }

    public function testNonAdminsCannotUseAdminEndpoints(): void
    {
        LtiTool::factory()
            ->slug('the-tool')
            ->extra(LtiToolExtra::factory()->slug('the-extra')->admin())
            ->create();

        $this->actingAs(User::factory()->create())
            ->get('/content/create/the-tool/the-extra')
            ->assertForbidden();
    }

    public function testCannotAccessAdminsAsNonAdmin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/admins')
            ->assertForbidden();
    }

    public function testAdminCanSearchContentExclusions(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get('/admin/content-exclusions/search')
            ->assertOk();
    }

    public function testAdminCanSearchMultipleContentExclusionsByIds(): void
    {
        $user = User::factory()->admin()->create();
        $content1 = \App\Models\Content::factory()->hasVersions(1)->create();
        $content2 = \App\Models\Content::factory()->hasVersions(1)->create();

        // Without spaces
        $this->actingAs($user)
            ->get('/admin/content-exclusions/search?contentId=' . $content1->id . ',' . $content2->id)
            ->assertOk()
            ->assertSee($content1->id)
            ->assertSee($content2->id);

        // With spaces and non-existent ID
        $this->actingAs($user)
            ->get('/admin/content-exclusions/search?contentId=' . $content1->id . ', ' . $content2->id . ', non_existent')
            ->assertOk()
            ->assertSee($content1->id)
            ->assertSee($content2->id);
    }

    public function testAdminCanSearchMultipleContentExclusionsByTitles(): void
    {
        $user = User::factory()->admin()->create();
        $content1 = \App\Models\Content::factory()->hasVersions(1, ['title' => 'First Content Title'])->create();
        $content2 = \App\Models\Content::factory()->hasVersions(1, ['title' => 'Second Content Title'])->create();

        // Without spaces
        $this->actingAs($user)
            ->get('/admin/content-exclusions/search?title=' . urlencode('First Content Title,Second Content Title'))
            ->assertOk()
            ->assertSee('First Content Title')
            ->assertSee('Second Content Title');

        // With spaces and short title filtered out
        $this->actingAs($user)
            ->get('/admin/content-exclusions/search?title=' . urlencode('First Content Title, Second Content Title, AB'))
            ->assertOk()
            ->assertSee('First Content Title')
            ->assertSee('Second Content Title');
    }

    public function testAdminCanSeeExcludedMarkInSearchResults(): void
    {
        $user = User::factory()->admin()->create();
        $content = \App\Models\Content::factory()->hasVersions(1, ['title' => 'Excluded Content Title', 'published' => true])->create();
        \App\Models\ContentExclusion::create([
            'content_id' => $content->id,
            'exclude_from' => 'library_translation_update',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/admin/content-exclusions/search?title=' . urlencode('Excluded Content Title'))
            ->assertOk()
            ->assertSee('Excluded Content Title')
            ->assertSee('Content type translation update')
            ->assertSee('table-warning');
    }

    public function testAdminContentExclusionsSearchPaginatesFiftyItemsByDefault(): void
    {
        $user = User::factory()->admin()->create();
        \App\Models\Content::factory()
            ->count(55)
            ->hasVersions(1, ['title' => 'Pagination Matching Title', 'published' => true])
            ->create();

        $response = $this->actingAs($user)
            ->get('/admin/content-exclusions/search?title=' . urlencode('Pagination Matching Title'))
            ->assertOk();

        /** @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\Content> $resultsPaginator */
        $resultsPaginator = $response->viewData('resultsPaginator');
        $this->assertNotNull($resultsPaginator);
        $this->assertSame(50, $resultsPaginator->perPage());
        $this->assertSame(55, $resultsPaginator->total());
        $this->assertCount(50, $resultsPaginator->items());
    }

    public function testAdminCanSelectResultsPerPageInTitleSearch(): void
    {
        $user = User::factory()->admin()->create();
        \App\Models\Content::factory()
            ->count(30)
            ->hasVersions(1, ['title' => 'Per Page Matching Title', 'published' => true])
            ->create();

        $response = $this->actingAs($user)
            ->get('/admin/content-exclusions/search?title=' . urlencode('Per Page Matching Title') . '&perPage=25')
            ->assertOk()
            ->assertSee('name="perPage"', false);

        /** @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\Content> $resultsPaginator */
        $resultsPaginator = $response->viewData('resultsPaginator');
        $this->assertNotNull($resultsPaginator);
        $this->assertSame(25, $resultsPaginator->perPage());
        $this->assertSame(30, $resultsPaginator->total());
        $this->assertCount(25, $resultsPaginator->items());
    }

    public function testAdminCanExcludeAlreadyExcludedContentFromIdSearchResults(): void
    {
        $user = User::factory()->admin()->create();
        $content1 = \App\Models\Content::factory()->hasVersions(1)->create();
        $content2 = \App\Models\Content::factory()->hasVersions(1)->create();

        \App\Models\ContentExclusion::create([
            'content_id' => $content1->id,
            'exclude_from' => 'library_translation_update',
            'user_id' => $user->id,
        ]);

        // Without excludeExcluded
        $responseWithoutFilter = $this->actingAs($user)
            ->get('/admin/content-exclusions/search?contentId=' . $content1->id . ',' . $content2->id)
            ->assertOk();
        /** @var \Illuminate\Support\Collection<int, \App\Models\Content> $resultsWithoutFilter */
        $resultsWithoutFilter = $responseWithoutFilter->viewData('results');
        $this->assertTrue($resultsWithoutFilter->contains('id', $content1->id));
        $this->assertTrue($resultsWithoutFilter->contains('id', $content2->id));

        // With excludeExcluded=1
        $responseWithFilter = $this->actingAs($user)
            ->get('/admin/content-exclusions/search?contentId=' . $content1->id . ',' . $content2->id . '&excludeExcluded=1')
            ->assertOk()
            ->assertSee('name="excludeExcluded"', false);
        /** @var \Illuminate\Support\Collection<int, \App\Models\Content> $resultsWithFilter */
        $resultsWithFilter = $responseWithFilter->viewData('results');
        $this->assertFalse($resultsWithFilter->contains('id', $content1->id));
        $this->assertTrue($resultsWithFilter->contains('id', $content2->id));
    }

    public function testAdminCanExcludeAlreadyExcludedContentFromTitleSearchResults(): void
    {
        $user = User::factory()->admin()->create();
        $content1 = \App\Models\Content::factory()->hasVersions(1, ['title' => 'Common Search Topic One', 'published' => true])->create();
        $content2 = \App\Models\Content::factory()->hasVersions(1, ['title' => 'Common Search Topic Two', 'published' => true])->create();

        \App\Models\ContentExclusion::create([
            'content_id' => $content1->id,
            'exclude_from' => 'library_translation_update',
            'user_id' => $user->id,
        ]);

        // Without excludeExcluded
        $responseWithoutFilter = $this->actingAs($user)
            ->get('/admin/content-exclusions/search?title=' . urlencode('Common Search Topic'))
            ->assertOk();
        /** @var \Illuminate\Support\Collection<int, \App\Models\Content> $resultsWithoutFilter */
        $resultsWithoutFilter = $responseWithoutFilter->viewData('results');
        $this->assertTrue($resultsWithoutFilter->contains('id', $content1->id));
        $this->assertTrue($resultsWithoutFilter->contains('id', $content2->id));

        // With excludeExcluded=1
        $responseWithFilter = $this->actingAs($user)
            ->get('/admin/content-exclusions/search?title=' . urlencode('Common Search Topic') . '&excludeExcluded=1')
            ->assertOk()
            ->assertSee('name="excludeExcluded"', false);
        /** @var \Illuminate\Support\Collection<int, \App\Models\Content> $resultsWithFilter */
        $resultsWithFilter = $responseWithFilter->viewData('results');
        $this->assertFalse($resultsWithFilter->contains('id', $content1->id));
        $this->assertTrue($resultsWithFilter->contains('id', $content2->id));
    }
}
