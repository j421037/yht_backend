<?php

use Faker\Generator as Faker;

$factory->define(App\CustomerNote::class, function (Faker $faker) {
    return [
        //
        "user_id"   => 98,
        "action"    => 3
    ];
});
