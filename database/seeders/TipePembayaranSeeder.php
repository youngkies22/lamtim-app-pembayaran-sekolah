<?php

namespace Database\Seeders;

use App\Models\LamtimTipePembayaran;
use Illuminate\Database\Seeder;

class TipePembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipeData = [
            [
                'kode' => 'TUNAI',
                'nama' => 'Tunai',
                'deskripsi' => 'Pembayaran secara tunai',
                'urutan' => 1,
                'isActive' => 1,
            ],
            [
                'kode' => 'TRANSFER',
                'nama' => 'Transfer Bank',
                'deskripsi' => 'Pembayaran melalui transfer bank',
                'urutan' => 2,
                'isActive' => 1,
            ],
            [
                'kode' => 'E-WALLET',
                'nama' => 'E-Wallet',
                'deskripsi' => 'Pembayaran melalui e-wallet (OVO, DANA, GoPay, dll)',
                'urutan' => 3,
                'isActive' => 1,
            ],
            [
                'kode' => 'QRIS',
                'nama' => 'QRIS',
                'deskripsi' => 'Pembayaran melalui QRIS',
                'urutan' => 4,
                'isActive' => 1,
            ],
        ];

        foreach ($tipeData as $data) {
            LamtimTipePembayaran::firstOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }

        $this->command->info('Tipe Pembayaran berhasil dibuat: ' . count($tipeData) . ' tipe');
    }
}
