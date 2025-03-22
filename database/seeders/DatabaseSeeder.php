<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (User::count()) {
            return;
        }

        // TODO: pass env variable on docker run to create it ?
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'avatar' => '/images/empty-user.jpg',
            'password' => Hash::make(2222)
        ]);
    }
}
