<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        User::factory()->create([
            'name' => 'Deni',
            'email' => 'deni@example.com',
        ]);
        User::factory()->create([
            'name' => 'Hasby',
            'email' => 'hasby@example.com',
        ]);
        User::factory()->create([
            'name' => 'Resti',
            'email' => 'resti@example.com',
        ]);
        User::factory()->create([
            'name' => 'fitri',
            'email' => 'fitri@example.com',
        ]);
        User::factory()->create([
            'name' => 'ayu',
            'email' => 'ayu@example.com',
        ]);
    }
}
