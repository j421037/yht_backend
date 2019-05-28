<?php

use Faker\Generator as Faker;

$factory->define(App\Customer::class, function (Faker $faker) {
    return [
        //
        "name"              => $faker->name,
        "create_user_id"    => 98,
        "phone"             => $faker->phoneNumber,
        "publish"           => 1,
        "wechat"            => str_random(10),
        "province"          => 20,
        "city"              => 234,
        "area"              => 2331
    ];
});
