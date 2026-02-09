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
        // Create default admin user
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'username' => 'admin',
                'name' => 'Admin Lamtim',
                'email' => 'lamtim@gmail.com',
                'password' => Hash::make('lamtim2025'),
                'role' => 1, // Admin
                'isActive' => 1,
                'email_verified_at' => now(),
            ]
        );

        // Create operator user
        User::firstOrCreate(
            ['username' => 'operator'],
            [
                'username' => 'operator',
                'name' => 'Operator SPP',
                'email' => 'operator@gmail.com',
                'password' => Hash::make('operator2025'),
                'role' => 2, // Operator
                'isActive' => 1,
                'email_verified_at' => now(),
            ]
        );

        // Create kepala sekolah user
        User::firstOrCreate(
            ['username' => 'kepsek'],
            [
                'username' => 'kepsek',
                'name' => 'Kepala Sekolah',
                'email' => 'kepsek@gmail.com',
                'password' => Hash::make('kepsek2025'),
                'role' => 3, // Kepsek
                'isActive' => 1,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Users berhasil dibuat:');
        $this->command->info('Admin: admin / lamtim2025');
        $this->command->info('Operator: operator / operator2025');
        $this->command->info('Kepsek: kepsek / kepsek2025');
    }
}
