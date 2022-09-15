<?php

use Illuminate\Database\Migrations\Migration;
use App\H5POption;

class AddNdlaCssTimestamp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        H5POption::updateOrCreate([
            'option_name' => H5POption::NDLA_CUSTOM_CSS_TIMESTAMP
        ], [
            'option_value' => \Carbon\Carbon::now()->toAtomString(),
            'autoload' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $ndlaCustomCssTimestamp = H5POption::where('option_name', H5POption::NDLA_CUSTOM_CSS_TIMESTAMP)->first();
        if ($ndlaCustomCssTimestamp) {
            $ndlaCustomCssTimestamp->delete();
        }
    }
}
