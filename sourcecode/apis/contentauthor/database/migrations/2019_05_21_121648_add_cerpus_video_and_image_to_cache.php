<?php

use App\H5PLibrariesHubCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCerpusVideoAndImageToCache extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ([
                     array('name' => 'H5P.CerpusImage','major_version' => '1','minor_version' => '0','patch_version' => '0','h5p_major_version' => '1','h5p_minor_version' => '19','title' => 'Image','summary' => 'Add a image as a separate content type','description' => 'Simply add images as content.','is_recommended' => '0','popularity' => '51','screenshots' => '','license' => '{"id":"MIT","attributes":{"useCommercially":true,"modifiable":true,"distributable":true,"sublicensable":true,"canHoldLiable":false,"mustIncludeCopyright":true,"mustIncludeLicense":true}}','example' => '','tutorial' => '','keywords' => '["image"]','categories' => '["Multimedia"]','owner' => 'Cerpus','icon' => ''),
                     array('name' => 'H5P.CerpusVideo','major_version' => '1','minor_version' => '0','patch_version' => '0','h5p_major_version' => '1','h5p_minor_version' => '19','title' => 'Video','summary' => 'Upload a video or link to Youtube','description' => 'Use videos in you content','is_recommended' => '0','popularity' => '52','screenshots' => '','license' => '{"id":"MIT","attributes":{"useCommercially":true,"modifiable":true,"distributable":true,"sublicensable":true,"canHoldLiable":false,"mustIncludeCopyright":true,"mustIncludeLicense":true}}','example' => '','tutorial' => '','keywords' => '["video","youtube"]','categories' => '["Multimedia"]','owner' => 'Cerpus','icon' => '')
                 ] as $item) {
            $cacheRow = H5PLibrariesHubCache::make();
            $cacheRow->create($item);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        H5PLibrariesHubCache::whereIn('name', ['H5P.CerpusImage', 'H5P.CerpusVideo'])->delete();
    }
}
