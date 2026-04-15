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


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'cell_phone' => $faker->phoneNumber,
        'user_name' => $faker->userName,
        'status' => 1,
        'rep_timestamp' => $faker->date('Y-m-d H:i:s'),
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret')
//        'remember_token' => str_random(10),
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'cell_phone' => $faker->phoneNumber,
        'user_name' => $faker->userName,
        'status' => 1,
        'rep_timestamp' => $faker->date('Y-m-d H:i:s'),
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'referrer_repid' => \App\User::withRole(2)->first()->idrep
    ];
}, 'affiliate');


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Offer::class, function (Faker\Generator $faker) {
    return [
        'offer_name' => $faker->company,
        'description' => $faker->paragraph(1),
        'url' => '#clickid#',
        'is_public' => $faker->numberBetween(-1, 3),
        'payout' => $faker->randomFloat(2, '0.10', '5.00'),
        'status' => 1,
        'offer_timestamp' => $faker->date('Y-m-d H:i:s'),
        'campaign_id' => \App\Campaign::all()->first()->id
    ];
});


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Click::class, function (Faker\Generator $faker) {
    return [
        'first_timestamp' => $faker->dateTimeBetween('-12 days', '+5 days'),
        'ip_address' => $faker->ipv4,
        'browser_agent' => $faker->userAgent,
        'click_type' => $faker->numberBetween(-1, 3),
    ];
});


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Conversion::class, function (Faker\Generator $faker) {
    return [
        'timestamp' => $faker->dateTimeBetween('-12 days', '+5 days'),
        'paid' => $faker->randomFloat(2, '0.10', '5.00')
    ];
});


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Salary::class, function (Faker\Generator $faker) {
    return [
        'timestamp' => $faker->dateTimeBetween('-3 months')->format('U'),
        'salary' => $faker->numberBetween(200, 500),
        'last_update' => $faker->dateTimeBetween('- 3 months')->format('U'),
        'status' => 1
    ];
});


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\PendingConversion::class, function (Faker\Generator $faker) {
    return [
        'timestamp' => $faker->dateTimeBetween('-3 months')->format('Y-m-d H:i:s'),
        'converted' => $faker->numberBetween(-1, 2),
        'payout' => $faker->randomFloat(2, '0.10', '5.00'),
    ];
});


/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Deduction::class, function (Faker\Generator $faker) {
    return [
        'deduction_timestamp' => $faker->dateTimeBetween('-3 months')->format('Y-m-d H:i:s'),
    ];
});
