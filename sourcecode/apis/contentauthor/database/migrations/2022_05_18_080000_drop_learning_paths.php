<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropLearningPaths extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('learning_path_steps');
        Schema::dropIfExists('learning_paths');
    }

    public function down(): void {}
}
