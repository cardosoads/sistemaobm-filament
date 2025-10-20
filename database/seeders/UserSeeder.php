<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@sistemaobm.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // Garantir dados atualizados caso já exista
        $user->forceFill([
            'name' => 'Admin',
            'password' => Hash::make('admin123'),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();
    }
}