<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddH5pContext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Holy f***! Sorry for this pain in the ass. But there where no way around this s***...
        if (!DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            Schema::table('h5p_contents_user_data', function ($table) {
                $table->dropPrimary('cud_pk1');
            });

            Schema::table('h5p_contents_user_data', function ($table) {
                $table->bigIncrements('id')->first();
                $table->string('context', 40)->nullable()->default(null);
                $table->index('context');
                $table->unique(['content_id', 'user_id', 'sub_content_id', 'data_id', 'context'], 'cu_pid');
            });
        } else {
            Schema::drop('h5p_contents_user_data');
            Schema::create('h5p_contents_user_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('content_id')->unsigned();
                $table->integer('user_id')->unsigned();
                $table->integer('sub_content_id')->unsigned();
                $table->string('data_id', 127);
                $table->text('data');
                $table->boolean('preload')->default(0);
                $table->boolean('invalidate')->default(0);
                $table->dateTime('updated_at')->default('0000-00-00 00:00:00');
                $table->string('context', 40)->nullable()->default(null);
                $table->unique(['content_id', 'user_id', 'sub_content_id', 'data_id', 'context'], 'cu_pid');
            });
        }


        Schema::table('h5p_results', function (Blueprint $table) {
            $table->string('context', 40)->nullable()->default(null);
            $table->index(['content_id', 'user_id', 'context'], 'content_user_context');
            $table->index('context');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_contents_user_data', function ($table) {
            $table->dropColumn(["id", "context"]);
            if (!DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
                $table->dropUnique('cu_pid');
            }
            $table->primary(['content_id', 'user_id', 'sub_content_id', 'data_id'], 'cud_pk1');
        });

        Schema::table('h5p_results', function (Blueprint $table) {
            if (!DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
                $table->dropIndex("content_user_context");
                $table->dropIndex(["context"]);
            }
            $table->dropColumn("context");
        });
    }
}
