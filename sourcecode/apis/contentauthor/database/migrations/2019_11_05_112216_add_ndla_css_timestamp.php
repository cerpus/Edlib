<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('h5p_options')->insert([
            [
                'option_name' => 'NDLA_CUSTOM_CSS_TIMESTAMP',
                'option_value' => Carbon::now()->toAtomString(),
                'autoload' => 0,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('h5p_options')
            ->where('option_name', 'NDLA_CUSTOM_CSS_TIMESTAMP')
            ->delete();
    }
};
