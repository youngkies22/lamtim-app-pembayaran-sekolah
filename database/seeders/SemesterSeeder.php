<?php

namespace Database\Seeders;

use App\Models\LamtimSemester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Semester Ganjil
        LamtimSemester::firstOrCreate(
            ['kode' => 'Ganjil'],
            [
                'kode' => 'Ganjil',
                'nama' => 'Semester Ganjil',
                'namaSingkat' => 'Ganjil',
                'isActive' => 1,
            ]
        );

        // Create Semester Genap
        LamtimSemester::firstOrCreate(
            ['kode' => 'Genap'],
            [
                'kode' => 'Genap',
                'nama' => 'Semester Genap',
                'namaSingkat' => 'Genap',
                'isActive' => 1,
            ]
        );

        $this->command->info('Semester berhasil dibuat: Ganjil dan Genap');
    }
}
