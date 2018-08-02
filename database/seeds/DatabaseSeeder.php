<?php

use App\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesSeeder::class,
        ]);
        User::createFromValues('John Doe', 'demo@demo.com', 'password')->assignRole('administrator');
    }
}
