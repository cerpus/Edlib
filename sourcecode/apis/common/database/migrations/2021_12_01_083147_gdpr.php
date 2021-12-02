<?php

use App\Models\Application;
use App\Models\GdprRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Gdpr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gdpr_requests', function (Blueprint $schema) {
            $schema->increments('id');
            $schema->foreignIdFor(Application::class);
            $schema->string('request_id')->nullable();
            $schema->string('user_id');
            $schema->timestamps();
        });
        Schema::create('gdpr_request_completed_steps', function (Blueprint $schema) {
            $schema->increments('id');
            $schema->foreignIdFor(GdprRequest::class);
            $schema->string('service_name');
            $schema->string('step_name');
            $schema->string('message')->nullable();
            $schema->timestamps();
            $schema->unique(['gdpr_request_id', 'service_name', 'step_name'], 'request_service_step_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gdpr_requests');
        Schema::dropIfExists('gdpr_request_completed_steps');
    }
}
