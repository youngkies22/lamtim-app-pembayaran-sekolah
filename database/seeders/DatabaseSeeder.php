<?php

namespace Database\Seeders;

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
        // Order matters for foreign key constraints
        $this->call([
            // 1. Users first (needed for createdBy, updatedBy, etc.)
            UserSeeder::class,
            
            // 2. Master data (independent tables first)
            LamtimAgamaSeeder::class,
            SemesterSeeder::class, // Semester (Ganjil, Genap)
            MasterDataSeeder::class, // Sekolah, Jurusan, Rombel, Kelas, Tahun Ajaran
            
            // 3. Pembayaran master data (independent)
            TipePembayaranSeeder::class,
            KategoriPembayaranSeeder::class,
            JenisPembayaranSeeder::class,
            
            // 4. Siswa data (depends on Agama)
            LamtimSiswaSeeder::class,
            
            // 5. Siswa-Rombel mapping (depends on Siswa and Rombel)
            SiswaRombelSeeder::class,
        ]);
    }
}
