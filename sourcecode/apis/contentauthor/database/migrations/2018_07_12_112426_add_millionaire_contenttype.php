<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMillionaireContenttype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('gametypes')) {
            Schema::create('gametypes', function (Blueprint $table) {
                $table->uuid('id');
                $table->string('title');
                $table->string('name');
                $table->smallInteger('major_version');
                $table->smallInteger('minor_version');

                $table->timestamps();

                $table->primary('id');
                $table->index('name');
            });
        }

        if (!Schema::hasTable('games')) {
            Schema::create('games', function (Blueprint $table) {
                $table->uuid('id');
                $table->string('gametype');
                $table->string('title');
                $table->string('language_code');
                $table->string('owner');
                $table->mediumText('game_settings');
                $table->boolean('is_private')->default(true);

                $table->timestamps();
                $table->softDeletes();

                $table->primary('id');
                $table->index('title');
                $table->index('owner');
                $table->index('gametype');

                $table->foreign('gametype')->references('id')->on('gametypes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
        Schema::dropIfExists('gametypes');
    }
}
