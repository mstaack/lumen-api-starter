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

$factory->define(App\Article::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->text(80),
        'text' => $faker->text(300),
    ];
});
