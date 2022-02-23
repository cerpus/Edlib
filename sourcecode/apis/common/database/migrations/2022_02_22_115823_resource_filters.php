<?php

use App\Models\SavedFilter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ResourceFilters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saved_filters', function (Blueprint $schema) {
            $schema->increments('id');
            $schema->string('name');
            $schema->string('user_id');
            $schema->timestamps();
        });
        Schema::create('saved_filter_choices', function (Blueprint $schema) {
            $schema->increments('id');
            $schema->foreignIdFor(SavedFilter::class);
            $schema->string('group_name');
            $schema->string('value');
            $schema->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saved_filters');
        Schema::dropIfExists('saved_filter_choices');
    }
}
