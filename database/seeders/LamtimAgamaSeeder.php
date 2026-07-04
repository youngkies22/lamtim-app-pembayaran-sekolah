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
            ['kode' => 'ISLAM', 'nama' => 'ISLAM'],
            ['kode' => 'KRISTEN', 'nama' => 'KRISTEN'],
            ['kode' => 'KATOLIK', 'nama' => 'KATOLIK'],
            ['kode' => 'HINDU', 'nama' => 'HINDU'],
            ['kode' => 'BUDDHA', 'nama' => 'BUDDHA'],
            ['kode' => 'KONGHUCU', 'nama' => 'KONG HU CU'],
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
