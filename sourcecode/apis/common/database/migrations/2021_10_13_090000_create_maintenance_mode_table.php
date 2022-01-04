<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceModeTable extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_mode', function (Blueprint $schema) {
            $schema->increments('id');
            $schema->boolean('enabled')->default(false);
            $schema->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_mode');
    }
}
