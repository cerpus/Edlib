<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::update(
            'UPDATE users SET theme = ? WHERE theme = ?',
            [
                null,
                'light',
            ],
        );
    }

    public function down(): void
    {
        // N/A
    }
};
