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

$factory->define(\App\User::class, function (Faker\Generator $faker) {
    return [
        'auth_id' => $faker->uuid,
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});

$factory->define(App\ArticleCollaborator::class, function (Faker\Generator $faker) {
    return [
        'article_id' => $faker->uuid,
        'email' => $faker->email,
    ];
});

$factory->define(App\H5PCollaborator::class, function (Faker\Generator $faker) {
    return [
        'h5p_id' => $faker->numberBetween(),
        'email' => $faker->email,
    ];
});

$factory->define(App\File::class, function (Faker\Generator $faker) {
    return [
        'article_id' => $faker->uuid,
        'name' => $faker->uuid.'.jpg',
        'original_name' => $faker->slug(3).'.'.$faker->fileExtension,
        'remember_token' => str_random(10),
    ];
});


$factory->define(App\ContentLock::class, function (Faker\Generator $faker) {
    return [
        'content_id' => $faker->uuid,
        'auth_id' => $faker->uuid,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});

$factory->define(App\ArticleCollaborator::class, function (Faker\Generator $faker) {
    return [
        'article_id' => $faker->uuid,
        'email' => $faker->email,
    ];
});

$factory->define(App\H5PContent::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->numberBetween(),
        'created_at' => $faker->unixTime,
        'updated_at' => $faker->unixTime,
        'user_id' => $faker->uuid,
        'title' => $faker->sentence,
        'library_id' => $faker->numberBetween(1, 100),
        'parameters' => json_encode([]),
        'filtered' => "",
        'slug' => $faker->slug(),
        'embed_type' => 'div',
        'disable' => 0,
        'content_type' => null,
        'author' => null,
        'license' => 'BY',
        'keywords' => null,
        'description' => null,
        'is_private' => 1,
        'version_id' => null,
        'max_score' => 0,
        'content_create_mode' => 'unitTest',
        'is_published' => 0,
    ];
});

$factory->define(App\H5PContentLibrary::class, function (Faker\Generator $faker) {
    return [
        'content_id' => $faker->numberBetween(),
        'library_id' => $faker->numberBetween(),
        'dependency_type' => 'preloaded',
        'weight' => $faker->numberBetween(0, 100),
        'drop_css' => 0,
    ];
});

$factory->define(App\H5PLibrary::class, function (Faker\Generator $faker) {
    return [
        'name' => 'H5P.Foobar',
        'title' => $faker->words(3, true),
        'major_version' => 1,
        'minor_version' => 2,
        'patch_version' => 3,
        'runnable' => true,
        'fullscreen' => true,
        'embed_types' => 'div',
        'semantics' => '[]',
        'tutorial_url' => 'http://burgerking.com',
    ];
});

$factory->define(App\CollaboratorContext::class, function (Faker\Generator $faker) {
    return [
        'system_id' => $faker->uuid,
        'context_id' => $faker->uuid,
        'type' => 'user',
        'collaborator_id' => $faker->uuid,
        'content_id' => $faker->uuid,
        'timestamp' => \Carbon\Carbon::now()->timestamp,
    ];
});

$factory->define(\App\H5PContentsUserData::class, function (\Faker\Generator $faker) {
    return [
        'id' => $faker->numberBetween(),
        'content_id' => $faker->numberBetween(),
        'user_id' => $faker->unique()->uuid,
        'sub_content_id' => 0,
        'data_id' => 'state',
        'data' => null,
        'preload' => 1,
        'invalidate' => 1,
        'updated_at' => $faker->unixTime,
        'context' => $faker->unique()->uuid,
    ];
});

$factory->define(\App\H5PContentsVideo::class, function (\Faker\Generator $faker) {
    return [
        'id' => $faker->numberBetween(),
        'h5p_content_id' => $faker->numberBetween(),
        'video_id' => $faker->uuid,
        'source_file' => 'videos/tmp_'.str_replace("-", "", substr($faker->uuid, rand(0, 15), 20)).'.mp4',
    ];
});

$factory->define(\App\QuestionSet::class, function (\Faker\Generator $faker) {
    return [
        'id' => $faker->uuid,
        'title' => $faker->sentence,
        'owner' => $faker->uuid,
        'external_reference' => null,
        'language_code' => $faker->languageCode,
    ];
});

$factory->define(\App\QuestionSetQuestion::class, function (\Faker\Generator $faker) {
    return [
        'id' => $faker->uuid,
        'question_set_id' => null,
        'question_text' => $faker->sentence,
        'image' => null,
        'order' => $faker->numberBetween(0, 1000),
    ];
});

$factory->define(\App\QuestionSetQuestionAnswer::class, function (\Faker\Generator $faker) {
    return [
        'id' => $faker->uuid,
        'question_id' => null,
        'answer_text' => $faker->sentence,
        'correct' => $faker->boolean,
        'image' => null,
        'order' => $faker->numberBetween(0, 1000),
    ];
});

$factory->define(\App\Gametype::class, function (\Faker\Generator $faker) {
    return [
        'id' => $faker->uuid,
        'title' => 'Game A 1.0',
        'name' => 'CERPUS.GameA',
        'major_version' => 1,
        'minor_version' => 0,
    ];
});

$factory->define(\App\Collaborator::class, function (\Faker\Generator $faker) {
    return [
        'email' => $faker->email,
    ];
});

$factory->define(\App\Game::class, function (\Faker\Generator $faker) {
    return [
        'id' => $faker->uuid,
        'gametype' => $faker->uuid,
        'title' => $faker->sentence,
        'language_code' => $faker->languageCode,
        'owner' => $faker->uuid,
        'game_settings' => json_encode(['setting' => true]),
        'version_id' => null,
    ];
});

$factory->define(\App\Link::class, function (\Faker\Generator $faker) {
    return [
        'id' => $faker->uuid,
        'title' => $faker->sentence,
        'link_url' => $faker->url,
        'link_type' => 'external_link',
        'link_text' => $faker->sentence,
    ];
});

$factory->define(App\H5PContentsMetadata::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->numberBetween(1, 10000),
        'content_id' => factory(\App\H5PContent::class)->create()->id,
        'authors' => '[]',
        'source' => null,
        'year_from' => null,
        'year_to' => null,
        'license' => null,
        'license_version' => null,
        'license_extras' => null,
        'author_comments' => null,
        'changes' => '[]',
    ];
});

$factory->define(App\H5PCollaborator::class, function (Faker\Generator $faker) {
    return [
        'h5p_id' => $faker->numberBetween(1, 10000),
        'email' => $faker->email,
    ];
});

$factory->define(App\H5PResult::class, function (\Faker\Generator $faker) {
    return [
        'content_id' => $faker->numberBetween(1, 10000),
        'user_id' => $faker->uuid,
        'score' => 0,
        'max_score' => 10,
        'opened' => \Carbon\Carbon::now()->subMinutes(1)->timestamp,
        'finished' => \Carbon\Carbon::now()->timestamp,
        'time' => 0,
        'context' => null,
    ];
});
