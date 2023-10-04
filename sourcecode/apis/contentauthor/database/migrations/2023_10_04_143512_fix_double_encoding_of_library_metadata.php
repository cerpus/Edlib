<?php

declare(strict_types=1);

use App\H5PLibrary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        H5PLibrary::whereNotNull('metadata_settings')
            ->orderBy('id')
            ->chunk(50, function (Collection $libraries) {
                $libraries->each(function (H5PLibrary $library) {
                    try {
                        $decoded = json_decode($library->metadata_settings, flags:JSON_THROW_ON_ERROR);
                        if (is_string($decoded)) {
                            $data = json_decode($decoded, flags: JSON_THROW_ON_ERROR);
                            if (is_object($data)) {
                                $library->metadata_settings = $decoded;
                                $library->save();
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
