<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourseExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_exports', function (Blueprint $table) {
            $table->string('ndla_id')->primary();
            $table->string('edstep_id');
            $table->string('edit_url')->nullable()->default(null);
            $table->unsignedInteger('modules_created')->default(0);
            $table->unsignedInteger('activities_created')->default(0);
            $table->text('message')->nullable()->default(null);

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
        Schema::dropIfExists('course_exports');
    }
}
