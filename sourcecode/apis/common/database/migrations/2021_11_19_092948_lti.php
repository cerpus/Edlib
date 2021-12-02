<?php

use App\Models\LtiKeySet;
use App\Models\LtiRegistration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Lti extends Migration
{
    public function up(): void
    {
        Schema::create('lti_key_sets', function (Blueprint $schema) {
            $schema->increments('id');
            $schema->timestamps();
        });
        Schema::create('lti_keys', function (Blueprint $schema) {
            $schema->increments('id');
            $schema->foreignIdFor(LtiKeySet::class);
            $schema->string('algorithm', 10);
            $schema->text('private_key');
            $schema->text('public_key');
            $schema->timestamps();
        });
        Schema::create('lti_registrations', function (Blueprint $schema) {
            $schema->increments('id');
            $schema->string('issuer')->nullable();
            $schema->string('client_id')->nullable();
            $schema->string('platform_login_auth_endpoint')->nullable();
            $schema->string('platform_auth_token_endpoint')->nullable();
            $schema->string('platform_key_set_endpoint')->nullable();
            $schema->foreignIdFor(LtiKeySet::class)->nullable();
            $schema->timestamps();
        });
        Schema::create('lti_deployments', function (Blueprint $schema) {
            $schema->string('deployment_id');
            $schema->foreignIdFor(LtiRegistration::class);
            $schema->timestamps();
            $schema->primary(['deployment_id', 'lti_registration_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lti_key_sets');
        Schema::dropIfExists('lti_keys');
        Schema::dropIfExists('lti_registrations');
        Schema::dropIfExists('lti_deployments');
    }
}
