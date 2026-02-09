<?php

namespace Database\Seeders;

use App\Models\LamtimSiswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LamtimSiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siswaData = [
            [
                'username' => 'siswa001',
                'password' => Hash::make('password'),
                'nama' => 'Ahmad Fauzi',
                'nis' => '2024001',
                'nisn' => '0012345678',
                'jsk' => 1,
                'isActive' => 1,
                'tahunAngkatan' => '2024',
            ],
            [
                'username' => 'siswa002',
                'password' => Hash::make('password'),
                'nama' => 'Siti Nurhaliza',
                'nis' => '2024002',
                'nisn' => '0012345679',
                'jsk' => 0,
                'isActive' => 1,
                'tahunAngkatan' => '2024',
            ],
            [
                'username' => 'siswa003',
                'password' => Hash::make('password'),
                'nama' => 'Budi Santoso',
                'nis' => '2024003',
                'nisn' => '0012345680',
                'jsk' => 1,
                'isActive' => 1,
                'tahunAngkatan' => '2024',
            ],
            [
                'username' => 'siswa004',
                'password' => Hash::make('password'),
                'nama' => 'Dewi Lestari',
                'nis' => '2024004',
                'nisn' => '0012345681',
                'jsk' => 0,
                'isActive' => 1,
                'tahunAngkatan' => '2024',
            ],
            [
                'username' => 'siswa005',
                'password' => Hash::make('password'),
                'nama' => 'Rizki Pratama',
                'nis' => '2024005',
                'nisn' => '0012345682',
                'jsk' => 1,
                'isActive' => 1,
                'tahunAngkatan' => '2024',
            ],
            [
                'username' => 'siswa006',
                'password' => Hash::make('password'),
                'nama' => 'Indah Permata',
                'nis' => '2024006',
                'nisn' => '0012345683',
                'jsk' => 0,
                'isActive' => 1,
                'tahunAngkatan' => '2024',
            ],
            [
                'username' => 'siswa007',
                'password' => Hash::make('password'),
                'nama' => 'Andi Wijaya',
                'nis' => '2024007',
                'nisn' => '0012345684',
                'jsk' => 1,
                'isActive' => 0,
                'tahunAngkatan' => '2023',
            ],
            [
                'username' => 'siswa008',
                'password' => Hash::make('password'),
                'nama' => 'Fitri Handayani',
                'nis' => '2024008',
                'nisn' => '0012345685',
                'jsk' => 0,
                'isActive' => 1,
                'tahunAngkatan' => '2024',
            ],
            [
                'username' => 'siswa009',
                'password' => Hash::make('password'),
                'nama' => 'Muhammad Yusuf',
                'nis' => '2024009',
                'nisn' => '0012345686',
                'jsk' => 1,
                'isActive' => 1,
                'tahunAngkatan' => '2024',
            ],
            [
                'username' => 'siswa010',
                'password' => Hash::make('password'),
                'nama' => 'Nur Aini',
                'nis' => '2024010',
                'nisn' => '0012345687',
                'jsk' => 0,
                'isActive' => 1,
                'tahunAngkatan' => '2024',
            ],
        ];

        foreach ($siswaData as $data) {
            LamtimSiswa::firstOrCreate(
                ['username' => $data['username']],
                $data
            );
        }

        $this->command->info('Siswa data berhasil dibuat: ' . count($siswaData) . ' siswa');
    }
}
