<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublishDraftFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('h5p_contents', 'is_published')) {
            Schema::table('h5p_contents', function (Blueprint $table) {
                $table->boolean('is_published')->default(0);
            });
        }

        if (!Schema::hasColumn('articles', 'is_published')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->boolean('is_published')->default(0);
            });
        }

        if (!Schema::hasColumn('games', 'is_published')) {
            Schema::table('games', function (Blueprint $table) {
                $table->boolean('is_published')->default(0);
            });
        }

        if (!Schema::hasColumn('links', 'is_published')) {
            Schema::table('links', function (Blueprint $table) {
                $table->boolean('is_published')->default(0);
            });
        }

        if (!Schema::hasColumn('question_sets', 'is_published')) {
            Schema::table('question_sets', function (Blueprint $table) {
                $table->boolean('is_published')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('h5p_contents', function (Blueprint $table) {
            $table->dropColumn(['is_published']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['is_published']);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['is_published']);
        });

        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn(['is_published']);
        });

        Schema::table('question_sets', function (Blueprint $table) {
            $table->dropColumn(['is_published']);
        });
    }
}
