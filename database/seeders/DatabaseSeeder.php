<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        User::create([
            'name'      => 'Ravi Patel',
            'email'     => 'test1@test.com',
            'password'  => bcrypt('123456'),
        ]);
    }
}
