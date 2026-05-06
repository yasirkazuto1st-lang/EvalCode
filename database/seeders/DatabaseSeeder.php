<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'nim_nip' => 'Admin',
            'role' => 'Admin',
            'password' => \Illuminate\Support\Facades\Hash::make('Admin123'),
        ]);
    }
}
