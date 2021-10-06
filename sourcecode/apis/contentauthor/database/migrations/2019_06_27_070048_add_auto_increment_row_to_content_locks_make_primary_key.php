<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoIncrementRowToContentLocksMakePrimaryKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('content_locks', function (Blueprint $table) {
            $table->primary('content_id'); // Replication can cause trouble if you don't use the primary key for transactions.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_locks', function (Blueprint $table) {
            $table->dropPrimary(['content_id']);
        });
    }
}
