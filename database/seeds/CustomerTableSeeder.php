<?php

use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        factory(App\Customer::class, 5)->create()->each(function ($u) {
            $u->note()->save(factory(App\CustomerNote::class)->make());
        });
    }

}