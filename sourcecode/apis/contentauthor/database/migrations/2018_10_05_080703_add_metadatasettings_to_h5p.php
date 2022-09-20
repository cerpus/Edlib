<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetadatasettingsToH5p extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('h5p_libraries', function (Blueprint $table) {
            $table->boolean('has_icon')->default(0);
            $table->text('metadata_settings')->nullable();
            $table->text('add_to')->nullable();
        });

        Schema::create('h5p_contents_metadata', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('content_id');
            $table->longText('authors')->nullable();
            $table->string('source')->nullable();
            $table->unsignedInteger('year_from')->nullable();
            $table->unsignedInteger('year_to')->nullable();
            $table->string('license', 32)->nullable();
            $table->string('license_version', 10)->nullable();
            $table->longText('license_extras')->nullable();
            $table->longText('author_comments')->nullable();
            $table->longText('changes')->nullable();

            $table->foreign(['content_id'])->references('id')->on('h5p_contents');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_libraries', function (Blueprint $table) {
            $table->dropColumn([
                'has_icon',
                'metadata_settings',
                'add_to',
            ]);
        });
        Schema::dropIfExists('h5p_contents_metadata');
    }
}
