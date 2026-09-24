<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Http\Controllers\ContentController;
use Tests\TestCase;

#[CoversClass(ContentController::class)]
class ContentActionButtonsTest extends TestCase
{
    use RefreshDatabase;

    public function testActionButtonsEndpointReturnsPollingAttributesWhenUserCanEdit(): void
    {
        $owner = User::factory()->create();
        $holder = User::factory()->create(['name' => 'Lock Holder User']);

        $content = Content::factory()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $content->acquireLock($holder);
        $this->assertTrue($content->isLocked());

        $response = $this->actingAs($owner)->get(route('content.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
        ]));

        $response->assertOk();
        $response->assertSee('hx-trigger="every 5s"', escape: false);
        $response->assertSee('hx-get="' . e(route('content.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
            'forUser' => 0,
            'showDrafts' => 0,
        ])), escape: false);
        $response->assertSee('hx-swap="outerHTML"', escape: false);
        $response->assertSee('The lock is held by: Lock Holder User.');
    }

    public function testActionButtonsEndpointDoesNotReturnPollingAttributesWhenUserCannotEdit(): void
    {
        $owner = User::factory()->create();
        $holder = User::factory()->create(['name' => 'Lock Holder User']);
        $viewer = User::factory()->create();

        $content = Content::factory()
            ->shared()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $content->acquireLock($holder);
        $this->assertTrue($content->isLocked());

        $response = $this->actingAs($viewer)->get(route('content.action-buttons', [
            $content,
            'version' => $content->latestPublishedVersion->id,
        ]));

        $response->assertOk();
        $response->assertDontSee('hx-trigger="every', escape: false);
        $response->assertDontSee('hx-get="' . e(route('content.action-buttons', [
            $content,
            'version' => $content->latestPublishedVersion->id,
        ])), escape: false);
    }

    public function testActionButtonsEndpointShowsLockWhenLockIsCreatedForEditor(): void
    {
        $owner = User::factory()->create();
        $holder = User::factory()->create(['name' => 'Lock Holder User']);

        $content = Content::factory()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $this->assertFalse($content->isLocked());

        // First request when unlocked: polling is active, no lock shown
        $responseUnlocked = $this->actingAs($owner)->get(route('content.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
        ]));

        $responseUnlocked->assertOk();
        $responseUnlocked->assertSee('hx-trigger="every 5s"', escape: false);
        $responseUnlocked->assertDontSee('The lock is held by');

        // Lock is acquired by another user
        $content->acquireLock($holder);
        $this->assertTrue($content->isLocked());

        // Next poll returns locked status dynamically
        $responseLocked = $this->actingAs($owner)->get(route('content.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
        ]));

        $responseLocked->assertOk();
        $responseLocked->assertSee('hx-trigger="every 5s"', escape: false);
        $responseLocked->assertSee('The lock is held by: Lock Holder User.');

        // Lock is released
        $content->releaseLock($holder);
        $this->assertFalse($content->isLocked());

        // Subsequent poll returns unlocked status dynamically
        $responseReleased = $this->actingAs($owner)->get(route('content.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
        ]));

        $responseReleased->assertOk();
        $responseReleased->assertSee('hx-trigger="every 5s"', escape: false);
        $responseReleased->assertDontSee('The lock is held by');
    }

    public function testDetailsActionButtonsEndpointReturnsPollingAttributesWhenUserCanEdit(): void
    {
        $owner = User::factory()->create();
        $holder = User::factory()->create(['name' => 'Detail Lock Holder']);

        $content = Content::factory()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $content->acquireLock($holder);
        $this->assertTrue($content->isLocked());

        $response = $this->actingAs($owner)->get(route('content.details.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
        ]));

        $response->assertOk();
        $response->assertSee('hx-trigger="every 5s"', escape: false);
        $response->assertSee('hx-get="' . e(route('content.details.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
            'explicitVersion' => 0,
        ])), escape: false);
        $response->assertSee('hx-swap="outerHTML"', escape: false);
    }

    public function testDetailsActionButtonsEndpointDoesNotReturnPollingAttributesWhenUserCannotEdit(): void
    {
        $owner = User::factory()->create();
        $holder = User::factory()->create(['name' => 'Detail Lock Holder']);
        $viewer = User::factory()->create();

        $content = Content::factory()
            ->shared()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $content->acquireLock($holder);
        $this->assertTrue($content->isLocked());

        $response = $this->actingAs($viewer)->get(route('content.details.action-buttons', [
            $content,
            'version' => $content->latestPublishedVersion->id,
        ]));

        $response->assertOk();
        $response->assertDontSee('hx-trigger="every', escape: false);
        $response->assertDontSee('hx-get="' . e(route('content.details.action-buttons', [
            $content,
            'version' => $content->latestPublishedVersion->id,
        ])), escape: false);
    }

    public function testDetailsActionButtonsEndpointShowsLockWhenLockIsCreatedForEditor(): void
    {
        $owner = User::factory()->create();
        $holder = User::factory()->create(['name' => 'Detail Lock Holder']);

        $content = Content::factory()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $this->assertFalse($content->isLocked());

        // First request when unlocked: polling is active, no lock shown
        $responseUnlocked = $this->actingAs($owner)->get(route('content.details.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
        ]));

        $responseUnlocked->assertOk();
        $responseUnlocked->assertSee('hx-trigger="every 5s"', escape: false);
        $responseUnlocked->assertDontSee('The lock is held by Detail Lock Holder', escape: false);

        // Lock is acquired by another user
        $content->acquireLock($holder);
        $this->assertTrue($content->isLocked());

        // Next poll returns locked status dynamically
        $responseLocked = $this->actingAs($owner)->get(route('content.details.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
        ]));

        $responseLocked->assertOk();
        $responseLocked->assertSee('hx-trigger="every 5s"', escape: false);
        $responseLocked->assertSee('disabled', escape: false);

        // Lock is released
        $content->releaseLock($holder);
        $this->assertFalse($content->isLocked());

        // Subsequent poll returns unlocked status dynamically
        $responseReleased = $this->actingAs($owner)->get(route('content.details.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
        ]));

        $responseReleased->assertOk();
        $responseReleased->assertSee('hx-trigger="every 5s"', escape: false);
        $responseReleased->assertDontSee('disabled', escape: false);
    }

    public function testDetailsPageRendersPollingWhenUserCanEdit(): void
    {
        $owner = User::factory()->create();

        $content = Content::factory()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $response = $this->actingAs($owner)->get(route('content.details', $content));
        $response->assertOk();
        $response->assertSee('hx-trigger="every 5s"', escape: false);
        $response->assertSee('details-action-buttons', escape: false);
    }

    public function testDetailsPageDoesNotRenderPollingWhenUserCannotEdit(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $content = Content::factory()
            ->shared()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $response = $this->actingAs($viewer)->get(route('content.details', $content));
        $response->assertOk();
        $response->assertDontSee('hx-trigger="every', escape: false);
        $response->assertDontSee('details-action-buttons', escape: false);

        // Unauthenticated guest test
        $guestResponse = $this->get(route('content.details', $content));
        $guestResponse->assertOk();
        $guestResponse->assertDontSee('hx-trigger="every', escape: false);
        $guestResponse->assertDontSee('details-action-buttons', escape: false);
    }

    public function testListingRendersBulkPollingForEditableItemsOnly(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $content1 = Content::factory()
            ->shared()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $content2 = Content::factory()
            ->shared()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($viewer)
            ->create();

        $content1->searchable();
        $content2->searchable();

        // For $owner: content1 is editable, content2 is not editable
        $response = $this->actingAs($owner)->get(route('content.index'));
        $response->assertOk();

        // Both items have DOM IDs for action buttons
        $response->assertSee('id="action-buttons-' . $content1->id . '"', escape: false);
        $response->assertSee('id="action-buttons-' . $content2->id . '"', escape: false);

        // Bulk polling container exists with hx-swap="none" and hx-trigger="every 5s"
        $response->assertSee('content/bulk-action-buttons', escape: false);
        $response->assertSee('hx-trigger="every 5s"', escape: false);
        $response->assertSee('hx-swap="none"', escape: false);

        // Bulk polling URL only includes editable content1 ID, not uneditable content2 ID
        $response->assertSee('ids%5B0%5D=' . $content1->id, escape: false);
        $response->assertDontSee('ids%5B1%5D=' . $content2->id, escape: false);
    }

    public function testListingDoesNotRenderBulkPollingWhenNoItemsAreEditable(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $content = Content::factory()
            ->shared()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();
        $content->searchable();

        // Viewer cannot edit $content
        $response = $this->actingAs($viewer)->get(route('content.index'));
        $response->assertOk();
        $response->assertDontSee('content/bulk-action-buttons', escape: false);
        $response->assertDontSee('hx-trigger="every', escape: false);

        // Guest cannot edit $content
        $guestResponse = $this->get(route('content.index'));
        $guestResponse->assertOk();
        $guestResponse->assertDontSee('content/bulk-action-buttons', escape: false);
        $guestResponse->assertDontSee('hx-trigger="every', escape: false);
    }

    public function testBulkActionButtonsEndpointReturnsOobSwapsForEditableItems(): void
    {
        $owner = User::factory()->create();
        $holder = User::factory()->create(['name' => 'Batch Lock Holder']);

        $content1 = Content::factory()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $content2 = Content::factory()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        // Initially both are unlocked
        $responseUnlocked = $this->actingAs($owner)->get(route('content.bulk-action-buttons', [
            'ids' => [$content1->id, $content2->id],
        ]));

        $responseUnlocked->assertOk();
        $responseUnlocked->assertSee('id="action-buttons-' . $content1->id . '"', escape: false);
        $responseUnlocked->assertSee('hx-swap-oob="outerHTML:#action-buttons-' . $content1->id . '"', escape: false);
        $responseUnlocked->assertSee('id="action-buttons-' . $content2->id . '"', escape: false);
        $responseUnlocked->assertSee('hx-swap-oob="outerHTML:#action-buttons-' . $content2->id . '"', escape: false);
        $responseUnlocked->assertDontSee('The lock is held by');

        // Lock content1
        $content1->acquireLock($holder);
        $this->assertTrue($content1->isLocked());

        // Batch poll returns locked state for content1 and unlocked for content2
        $responseLocked = $this->actingAs($owner)->get(route('content.bulk-action-buttons', [
            'ids' => [$content1->id, $content2->id],
        ]));

        $responseLocked->assertOk();
        $responseLocked->assertSee('The lock is held by: Batch Lock Holder.');

        // Lock is released
        $content1->releaseLock($holder);
        $this->assertFalse($content1->isLocked());

        // Batch poll returns unlocked state again
        $responseReleased = $this->actingAs($owner)->get(route('content.bulk-action-buttons', [
            'ids' => [$content1->id, $content2->id],
        ]));

        $responseReleased->assertOk();
        $responseReleased->assertDontSee('The lock is held by');
    }

    public function testBulkActionButtonsEndpointDoesNotReturnItemsUserCannotEdit(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $content = Content::factory()
            ->shared()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $response = $this->actingAs($viewer)->get(route('content.bulk-action-buttons', [
            'ids' => [$content->id],
        ]));

        $response->assertOk();
        $this->assertEmpty(trim($response->getContent()));
    }

    public function testBulkActionButtonsWithEmptyIdsReturnsEmptyContent(): void
    {
        $viewer = User::factory()->create();

        $response = $this->actingAs($viewer)->get(route('content.bulk-action-buttons'));
        $response->assertOk();
        $this->assertEmpty(trim($response->getContent()));
    }

    public function testListingRendersCustomConfiguredPollingIntervalForEditor(): void
    {
        config(['features.list-polling-interval' => '10s']);

        $owner = User::factory()->create();

        $content = Content::factory()
            ->shared()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();
        $content->searchable();

        $response = $this->actingAs($owner)->get(route('content.index'));
        $response->assertOk();
        $response->assertSee('hx-trigger="every 10s"', escape: false);
    }

    public function testListingRendersNumericConfiguredPollingIntervalForEditor(): void
    {
        config(['features.list-polling-interval' => 15]);

        $owner = User::factory()->create();

        $content = Content::factory()
            ->shared()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();
        $content->searchable();

        $response = $this->actingAs($owner)->get(route('content.index'));
        $response->assertOk();
        $response->assertSee('hx-trigger="every 15s"', escape: false);
    }

    public function testListingRendersNoPollingWhenDisabled(): void
    {
        config(['features.list-polling-interval' => null]);

        $owner = User::factory()->create();

        $content = Content::factory()
            ->shared()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();
        $content->searchable();

        $response = $this->actingAs($owner)->get(route('content.index'));
        $response->assertOk();
        $response->assertDontSee('content/bulk-action-buttons', escape: false);
        $response->assertDontSee('hx-trigger="every', escape: false);

        // Also test with 'disabled'
        config(['features.list-polling-interval' => 'disabled']);
        $responseDisabled = $this->actingAs($owner)->get(route('content.index'));
        $responseDisabled->assertOk();
        $responseDisabled->assertDontSee('content/bulk-action-buttons', escape: false);
        $responseDisabled->assertDontSee('hx-trigger="every', escape: false);

        // Also test with 0
        config(['features.list-polling-interval' => 0]);
        $responseZero = $this->actingAs($owner)->get(route('content.index'));
        $responseZero->assertOk();
        $responseZero->assertDontSee('content/bulk-action-buttons', escape: false);
        $responseZero->assertDontSee('hx-trigger="every', escape: false);

        // Also test with false
        config(['features.list-polling-interval' => false]);
        $responseFalse = $this->actingAs($owner)->get(route('content.index'));
        $responseFalse->assertOk();
        $responseFalse->assertDontSee('content/bulk-action-buttons', escape: false);
        $responseFalse->assertDontSee('hx-trigger="every', escape: false);
    }

    public function testDetailsRendersCustomConfiguredPollingInterval(): void
    {
        config(['features.details-polling-interval' => '10s']);

        $owner = User::factory()->create();

        $content = Content::factory()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $response = $this->actingAs($owner)->get(route('content.details', $content));
        $response->assertOk();
        $response->assertSee('hx-trigger="every 10s"', escape: false);
        $response->assertSee('details-action-buttons', escape: false);

        // Also check the action buttons endpoint directly
        $endpointResponse = $this->actingAs($owner)->get(route('content.details.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
        ]));
        $endpointResponse->assertOk();
        $endpointResponse->assertSee('hx-trigger="every 10s"', escape: false);
    }

    public function testDetailsRendersNumericConfiguredPollingInterval(): void
    {
        config(['features.details-polling-interval' => 15]);

        $owner = User::factory()->create();

        $content = Content::factory()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $response = $this->actingAs($owner)->get(route('content.details', $content));
        $response->assertOk();
        $response->assertSee('hx-trigger="every 15s"', escape: false);

        $endpointResponse = $this->actingAs($owner)->get(route('content.details.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
        ]));
        $endpointResponse->assertOk();
        $endpointResponse->assertSee('hx-trigger="every 15s"', escape: false);
    }

    public function testDetailsRendersNoPollingWhenDisabled(): void
    {
        config(['features.details-polling-interval' => null]);

        $owner = User::factory()->create();

        $content = Content::factory()
            ->withVersion(ContentVersion::factory(['published' => true]))
            ->withUser($owner)
            ->create();

        $response = $this->actingAs($owner)->get(route('content.details', $content));
        $response->assertOk();
        $response->assertDontSee('hx-trigger="every', escape: false);

        $endpointResponse = $this->actingAs($owner)->get(route('content.details.action-buttons', [
            $content,
            'version' => $content->latestVersion->id,
        ]));
        $endpointResponse->assertOk();
        $endpointResponse->assertDontSee('hx-trigger="every', escape: false);

        // Also test with 'disabled'
        config(['features.details-polling-interval' => 'disabled']);
        $responseDisabled = $this->actingAs($owner)->get(route('content.details', $content));
        $responseDisabled->assertOk();
        $responseDisabled->assertDontSee('hx-trigger="every', escape: false);

        // Also test with 0
        config(['features.details-polling-interval' => 0]);
        $responseZero = $this->actingAs($owner)->get(route('content.details', $content));
        $responseZero->assertOk();
        $responseZero->assertDontSee('hx-trigger="every', escape: false);

        // Also test with false
        config(['features.details-polling-interval' => false]);
        $responseFalse = $this->actingAs($owner)->get(route('content.details', $content));
        $responseFalse->assertOk();
        $responseFalse->assertDontSee('hx-trigger="every', escape: false);
    }
}
