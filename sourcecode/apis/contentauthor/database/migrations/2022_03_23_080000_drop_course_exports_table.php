<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class DropCourseExportsTable extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('course_exports');
    }

    public function down(): void
    {
        // nothing to do!
    }
}
