<?php

namespace Database\Seeders;

use App\Models\LamtimSekolah;
use App\Models\LamtimJurusan;
use App\Models\LamtimRombel;
use App\Models\LamtimKelas;
use App\Models\LamtimTahunAjaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Tahun Ajaran
        $tahunAjaran = LamtimTahunAjaran::firstOrCreate(
            ['kode' => '2024-2025'],
            [
                'kode' => '2024-2025',
                'tahun' => '2024/2025',
                'tanggalMulai' => '2024-07-15',
                'tanggalSelesai' => '2025-06-30',
                'isActive' => 1,
            ]
        );

        LamtimTahunAjaran::firstOrCreate(
            ['kode' => '2025-2026'],
            [
                'kode' => '2025-2026',
                'tahun' => '2025/2026',
                'tanggalMulai' => '2025-07-15',
                'tanggalSelesai' => '2026-06-30',
                'isActive' => 0,
            ]
        );

        // Create Kelas
        $kelasData = [
            ['kode' => 'X', 'nama' => 'Kelas X'],
            ['kode' => 'XI', 'nama' => 'Kelas XI'],
            ['kode' => 'XII', 'nama' => 'Kelas XII'],
        ];

        $kelasList = [];
        foreach ($kelasData as $data) {
            $kelasList[$data['kode']] = LamtimKelas::firstOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }

        // Create Sekolah
        $sekolah = LamtimSekolah::firstOrCreate(
            ['npsn' => '10812345'],
            [
                'npsn' => '10812345',
                'kode' => 'SMK001',
                'nama' => 'SMK Negeri 1 Lampung Timur',
                'alamat' => 'Jl. Pendidikan No. 1, Lampung Timur',
                'kota' => 'Lampung Timur',
                'provinsi' => 'Lampung',
                'akreditasi' => 'A',
                'telepon' => '0721-123456',
                'email' => 'smkn1lamtim@gmail.com',
                'website' => 'https://smkn1lamtim.sch.id',
            ]
        );
        
        // Update kode jika sekolah sudah ada tapi belum punya kode
        if (!$sekolah->kode) {
            $sekolah->kode = 'SMK001';
            $sekolah->save();
        }

        // Create Jurusan
        $jurusanData = [
            ['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak'],
            ['kode' => 'TKJ', 'nama' => 'Teknik Komputer dan Jaringan'],
            ['kode' => 'MM', 'nama' => 'Multimedia'],
            ['kode' => 'AKL', 'nama' => 'Akuntansi dan Keuangan Lembaga'],
            ['kode' => 'OTKP', 'nama' => 'Otomatisasi dan Tata Kelola Perkantoran'],
        ];

        $jurusanList = [];
        foreach ($jurusanData as $data) {
            $jurusanList[$data['kode']] = LamtimJurusan::firstOrCreate(
                ['kode' => $data['kode'], 'idSekolah' => $sekolah->id],
                array_merge($data, ['idSekolah' => $sekolah->id])
            );
        }

        // Create Rombel for each Jurusan and Kelas combination
        $rombelCount = 1;
        foreach ($jurusanList as $jurusanKode => $jurusan) {
            foreach ($kelasList as $kelasKode => $kelas) {
                // Create 2 rombel per kelas per jurusan
                for ($i = 1; $i <= 2; $i++) {
                    $rombelKode = $kelasKode . '-' . $jurusanKode . '-' . $i;
                    $rombelNama = $kelasKode . ' ' . $jurusanKode . ' ' . $i;
                    
                    LamtimRombel::firstOrCreate(
                        ['kode' => $rombelKode],
                        [
                            'kode' => $rombelKode,
                            'nama' => $rombelNama,
                            'idSekolah' => $sekolah->id,
                            'idJurusan' => $jurusan->id,
                            'idKelas' => $kelas->id, // Set kelas untuk rombel
                            'isActive' => 1,
                        ]
                    );
                    $rombelCount++;
                }
            }
        }

        $this->command->info('Master data berhasil dibuat:');
        $this->command->info('- 2 Tahun Ajaran');
        $this->command->info('- 3 Kelas (X, XI, XII)');
        $this->command->info('- 1 Sekolah');
        $this->command->info('- ' . count($jurusanData) . ' Jurusan');
        $this->command->info('- ' . ($rombelCount - 1) . ' Rombel');
    }
}
