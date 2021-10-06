<?php
use Illuminate\Database\Migrations\Migration;

class IncreaseTextLicense extends Migration {
    public function up() {
        Schema::table('h5p_contents', function ($table) {
            $table->string('license', 255)->change();
        });
    }

    public function down() {
        Schema::table('h5p_contents', function ($table) {
            $table->string('license', 7)->change();
        });
    }
}
