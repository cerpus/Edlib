<?php

use Illuminate\Database\Migrations\Migration;
use App\H5PContent;

class AddEmptySlug extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        H5PContent::chunk(100, function ($contents) {
            foreach ($contents as $content) {
                if (empty($content->slug)) {
                    $content->slug = H5PCore::slugify($content['title']);
                    $content->save();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
}
