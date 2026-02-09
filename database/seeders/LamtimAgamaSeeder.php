<?php

namespace Database\Seeders;

use App\Models\LamtimAgama;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LamtimAgamaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agamaData = [
            ['kode' => 'ISLAM', 'nama' => 'Islam'],
            ['kode' => 'KRISTEN', 'nama' => 'Kristen'],
            ['kode' => 'KATOLIK', 'nama' => 'Katolik'],
            ['kode' => 'HINDU', 'nama' => 'Hindu'],
            ['kode' => 'BUDDHA', 'nama' => 'Buddha'],
            ['kode' => 'KONGHUCU', 'nama' => 'Khonghucu'],
        ];

        foreach ($agamaData as $agama) {
            LamtimAgama::updateOrCreate(
                ['kode' => $agama['kode']],
                [
                    'id' => Str::uuid()->toString(),
                    'nama' => $agama['nama'],
                ]
            );
        }
    }
}
