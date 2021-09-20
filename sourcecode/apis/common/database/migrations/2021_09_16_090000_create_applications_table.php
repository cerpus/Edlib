<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $schema) {
            $schema->uuid('id')->primary();
            $schema->text('name');
            $schema->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
}
