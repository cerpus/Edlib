<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use App\Models\Content;
use App\Models\ContentLicense;

$factory->define(Content::class, function ($faker) {
    $contentId = $faker->uuid();

    return [
        'site' => 'testsite',
        'content_id' => $contentId,
        'content_id_hash' => sha1($contentId),
        'name' => "Content $contentId",
    ];
});

$factory->define(ContentLicense::class, function ($faker) {
    return [
        'content_id' => 1,
        'license_id' => 'BY'
    ];
});
