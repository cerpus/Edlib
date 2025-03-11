<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Admin;

use App\Article;
use App\Http\Controllers\Admin\AdminArticleController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\View\View;
use Tests\TestCase;

class AdminArticleControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_viewFailedCalculations(): void
    {
        Article::factory()->create([
            'bulk_calculated' => Article::BULK_UPDATED,
        ]);
        $failedResource = Article::factory()->create([
            'bulk_calculated' => Article::BULK_FAILED,
        ]);

        $result = app(AdminArticleController::class)->viewFailedCalculations();
        $this->assertInstanceOf(View::class, $result);

        $data = $result->getData();
        $this->assertCount(1, $data['resources']);

        $resource = $data['resources']->first();
        $this->assertSame($failedResource->id, $resource->id);
    }
}
