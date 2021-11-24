<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationTable extends Migration
{
    public function up()
    {
        Schema::create('organizations', function (Blueprint $schema) {
            $schema->uuid('id')->primary();
            $schema->text('name');
            $schema->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizations');
    }
}
