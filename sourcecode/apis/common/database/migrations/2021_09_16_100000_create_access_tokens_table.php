<?php

use App\Models\Application;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessTokensTable extends Migration
{
    public function up(): void
    {
        Schema::create('access_tokens', function (Blueprint $schema) {
            $schema->uuid('id')->primary();
            $schema->text('name');
            $schema->string('token');
            $schema->foreignIdFor(Application::class);
            $schema->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_tokens');
    }
}
