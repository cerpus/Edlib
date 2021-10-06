<?php

use App\Article;

$factory->define(Article::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->uuid,
        'title' => $faker->text,
        'owner_id' => $faker->uuid,
        'content' => $faker->text,
        'original_id' => $faker->uuid,
        'parent_id' => null,
        'parent_version_id' => null,
        'version_id' => $faker->uuid
    ];
});

$factory->state(Article::class, "newly-created", function($faker){
    $id = $faker->uuid;
    return [
        "id" => $id,
        "original_id" => $id ,
        "version_id" => 1,
    ];
});

$factory->state(Article::class, "published", [
   "is_published" => true,
]);
$factory->state(Article::class, "unpublished", [
    "is_published" => false,
]);

$factory->state(Article::class, "listed", [
    "is_private" => false,
]);
$factory->state(Article::class, "unlisted", [
    "is_private" => true,
]);
