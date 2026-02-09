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
                'keterangan' => 'Pembayaran SPP bulanan',
                'isActive' => 1,
            ],
            [
                'kode' => 'PKL',
                'nama' => 'PKL (Praktik Kerja Lapangan)',
                'keterangan' => 'Pembayaran untuk kegiatan PKL',
                'isActive' => 1,
            ],
            [
                'kode' => 'UKOM',
                'nama' => 'UKOM (Uji Kompetensi)',
                'keterangan' => 'Pembayaran untuk uji kompetensi',
                'isActive' => 1,
            ],
            [
                'kode' => 'KI',
                'nama' => 'KI (Kunjungan Industri)',
                'keterangan' => 'Pembayaran untuk kunjungan industri',
                'isActive' => 1,
            ],
            [
                'kode' => 'LAINNYA',
                'nama' => 'Lainnya',
                'keterangan' => 'Pembayaran lainnya',
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
