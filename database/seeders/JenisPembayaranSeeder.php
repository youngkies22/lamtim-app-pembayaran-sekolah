<?php

namespace Database\Seeders;

use App\Models\LamtimJenisPembayaran;
use Illuminate\Database\Seeder;

class JenisPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisData = [
            [
                'kode' => 'SPP',
                'nama' => 'SPP (Sumbangan Pembinaan Pendidikan)',
                'deskripsi' => 'Pembayaran SPP bulanan',
                'urutan' => 1,
                'isActive' => 1,
            ],
            [
                'kode' => 'PKL',
                'nama' => 'PKL (Praktik Kerja Lapangan)',
                'deskripsi' => 'Pembayaran untuk kegiatan PKL',
                'urutan' => 2,
                'isActive' => 1,
            ],
            [
                'kode' => 'UKOM',
                'nama' => 'UKOM (Uji Kompetensi)',
                'deskripsi' => 'Pembayaran untuk uji kompetensi',
                'urutan' => 3,
                'isActive' => 1,
            ],
            [
                'kode' => 'KI',
                'nama' => 'KI (Kunjungan Industri)',
                'deskripsi' => 'Pembayaran untuk kunjungan industri',
                'urutan' => 4,
                'isActive' => 1,
            ],
            [
                'kode' => 'LAINNYA',
                'nama' => 'Lainnya',
                'deskripsi' => 'Pembayaran lainnya',
                'urutan' => 5,
                'isActive' => 1,
            ],
        ];

        foreach ($jenisData as $data) {
            LamtimJenisPembayaran::firstOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }

        $this->command->info('Jenis Pembayaran berhasil dibuat: ' . count($jenisData) . ' jenis');
    }
}
