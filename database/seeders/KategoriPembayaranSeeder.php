<?php

namespace Database\Seeders;

use App\Models\LamtimKategoriPembayaran;
use Illuminate\Database\Seeder;

class KategoriPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriData = [
            [
                'kode' => 'BULANAN',
                'nama' => 'Bulanan',
                'deskripsi' => 'Pembayaran yang dilakukan setiap bulan',
                'urutan' => 1,
                'isActive' => 1,
            ],
            [
                'kode' => 'TAMBAHAN',
                'nama' => 'Tambahan',
                'deskripsi' => 'Pembayaran tambahan di luar pembayaran rutin',
                'urutan' => 2,
                'isActive' => 1,
            ],
        ];

        foreach ($kategoriData as $data) {
            LamtimKategoriPembayaran::firstOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }

        $this->command->info('Kategori Pembayaran berhasil dibuat: ' . count($kategoriData) . ' kategori');
    }
}
