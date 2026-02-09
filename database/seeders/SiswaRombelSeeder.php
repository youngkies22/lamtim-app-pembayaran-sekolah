<?php

namespace Database\Seeders;

use App\Models\LamtimSiswa;
use App\Models\LamtimRombel;
use App\Models\LamtimSiswaRombel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiswaRombelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            DB::beginTransaction();

            // Get all active students
            $students = LamtimSiswa::where('isActive', 1)->get();
            
            if ($students->isEmpty()) {
                $this->command->warn('Tidak ada siswa aktif untuk di-mapping ke rombel');
                DB::commit();
                return;
            }

            // Get all active rombels
            $rombels = LamtimRombel::where('isActive', 1)->get();
            
            if ($rombels->isEmpty()) {
                $this->command->warn('Tidak ada rombel aktif untuk mapping siswa');
                DB::commit();
                return;
            }

            $mappedCount = 0;
            $rombelIndex = 0;
            $rombelCount = $rombels->count();

            foreach ($students as $student) {
                // Skip if already mapped
                $existingMapping = LamtimSiswaRombel::where('idSiswa', $student->id)->first();
                if ($existingMapping) {
                    continue;
                }

                // Get rombel (round-robin distribution)
                $rombel = $rombels[$rombelIndex % $rombelCount];
                $rombelIndex++;

                // Create mapping (idKelas sudah ada di rombel, tidak perlu disimpan di mapping)
                LamtimSiswaRombel::create([
                    'idSiswa' => $student->id,
                    'idRombel' => $rombel->id,
                ]);

                $mappedCount++;
            }

            DB::commit();

            $this->command->info("Mapping siswa-rombel berhasil dibuat: {$mappedCount} siswa");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error creating siswa-rombel mapping: ' . $e->getMessage());
            throw $e;
        }
    }
}
