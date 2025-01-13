<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::table('users')->count() > 0) {
            DB::update(<<<EOSQL
            UPDATE users SET facebook_id = NULL WHERE facebook_id IN (
                   SELECT facebook_id FROM (
                       SELECT facebook_id, COUNT(facebook_id) c
                       FROM users
                       GROUP BY facebook_id
                   ) q WHERE c > 1)
            EOSQL);

            DB::update(<<<EOSQL
            UPDATE users SET google_id = NULL WHERE google_id IN (
                   SELECT google_id FROM (
                       SELECT google_id, COUNT(google_id) c
                       FROM users
                       GROUP BY google_id
                   ) q WHERE c > 1)
            EOSQL);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('facebook_id');
            $table->unique('google_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['facebook_id']);
            $table->dropUnique(['google_id']);
        });
    }
};
