<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\H5PLibrary;
use App\Libraries\H5P\H5PViewConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class H5PAuthorStylesTest extends TestCase
{
    use RefreshDatabase;

    public function test_alterStyles_is_called_during_loadContent(): void
    {
        Session::put('adapterMode', 'ndla');

        $library = H5PLibrary::factory()->create(['name' => 'H5P.CoursePresentation']);
        $content = H5PContent::factory()->create([
            'library_id' => $library->id,
            'parameters' => '{"title":"Test"}',
        ]);

        $viewConfig = app(H5PViewConfig::class);
        $data = $viewConfig->loadContent($content->id)->getConfig();

        // Check if the custom CSS for CoursePresentation is included in the assets
        // Based on NDLAH5PAdapter::getLibraryCustomCss, it should add /css/H5P.CoursePresentation.css
        $found = false;
        foreach ($data->styles as $style) {
            if (str_contains($style, 'css/H5P.CoursePresentation.css')) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Custom CSS for H5P.CoursePresentation should be included in styles');
    }

    public function test_alterStyles_is_called_for_dependencies(): void
    {
        Session::put('adapterMode', 'ndla');

        $mainLibrary = H5PLibrary::factory()->create(['name' => 'H5P.Main']);
        $dependencyLibrary = H5PLibrary::factory()->create(['name' => 'H5P.InteractiveVideo']);

        $content = H5PContent::factory()->create([
            'library_id' => $mainLibrary->id,
            'parameters' => '{"title":"Test"}',
        ]);

        // Mock dependencies in the database for the content
        \Illuminate\Support\Facades\DB::table('h5p_contents_libraries')->insert([
            'content_id' => $content->id,
            'library_id' => $dependencyLibrary->id,
            'dependency_type' => 'preloaded',
            'weight' => 1,
            'drop_css' => 0
        ]);

        $viewConfig = app(H5PViewConfig::class);
        $data = $viewConfig->loadContent($content->id)->getConfig();

        $found = false;
        foreach ($data->styles as $style) {
            if (str_contains($style, 'css/H5P.InteractiveVideo.css')) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Custom CSS for dependency H5P.InteractiveVideo should be included in styles');
    }
}
