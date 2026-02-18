<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('h5p_libraries')
            ->select(['id', 'metadata_settings'])
            ->whereNotNull('metadata_settings')
            ->orderBy('id')
            ->chunk(50, function (Collection $libraries) {
                $libraries->each(function ($library) {
                    try {
                        $decoded = json_decode($library->metadata_settings, flags: JSON_THROW_ON_ERROR);
                        if (is_string($decoded)) {
                            $data = json_decode($decoded, flags: JSON_THROW_ON_ERROR);
                            if (is_object($data)) {
                                DB::table('h5p_libraries')
                                    ->where('id', $library->id)
                                    ->update([
                                        'metadata_settings' => $decoded,
                                    ]);
                            }
                        }
                    } catch (JsonException) {
                        // Ignore and continue
                    }
                });
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Not available
    }
};
