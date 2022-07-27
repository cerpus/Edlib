<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIsPrivateToLinksTable extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->boolean('is_private')->default(false);
        });
    }
    
    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('is_private');
        });
    }
}
