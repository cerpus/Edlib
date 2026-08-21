<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Eloquent has no native support for composite primary keys. Some databases
        // still have the original composite primary key from before this table
        // gained a surrogate `id` column; others already have the `id` primary key
        // from table creation, so only replace the primary key where it's still composite.
        if (!$this->hasCompositePrimaryKey('h5p_libraries_languages')) {
            return;
        }

        Schema::table('h5p_libraries_languages', function (Blueprint $table) {
            $table->dropPrimary(['library_id', 'language_code']);
        });

        Schema::table('h5p_libraries_languages', function (Blueprint $table) {
            $table->increments('id')->first();
            $table->unique(['library_id', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ($this->hasCompositePrimaryKey('h5p_libraries_languages')) {
            return;
        }

        Schema::table('h5p_libraries_languages', function (Blueprint $table) {
            $table->dropUnique(['library_id', 'language_code']);
            $table->dropColumn('id');
        });

        Schema::table('h5p_libraries_languages', function (Blueprint $table) {
            $table->primary(['library_id', 'language_code']);
        });
    }

    private function hasCompositePrimaryKey(string $table): bool
    {
        $primaryKey = collect(Schema::getIndexes($table))->firstWhere('primary', true);

        return count($primaryKey['columns'] ?? []) > 1;
    }
};
