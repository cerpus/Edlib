<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropNorgesfilmsTable extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('norgesfilms');
    }
}
