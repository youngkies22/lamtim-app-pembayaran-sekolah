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
                'keterangan' => 'Pembayaran secara tunai',
                'isActive' => 1,
            ],
            [
                'kode' => 'TRANSFER',
                'nama' => 'Transfer Bank',
                'keterangan' => 'Pembayaran melalui transfer bank',
                'isActive' => 1,
            ],
            [
                'kode' => 'E-WALLET',
                'nama' => 'E-Wallet',
                'keterangan' => 'Pembayaran melalui e-wallet (OVO, DANA, GoPay, dll)',
                'isActive' => 1,
            ],
            [
                'kode' => 'QRIS',
                'nama' => 'QRIS',
                'keterangan' => 'Pembayaran melalui QRIS',
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
