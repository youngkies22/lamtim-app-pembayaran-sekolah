<?php

namespace App\Services;

use App\Helpers\CacheHelper;
use App\Models\LamtimInvoice;
use App\Models\LamtimJenisPembayaran;
use App\Models\LamtimJurusan;
use App\Models\LamtimKategoriPembayaran;
use App\Models\LamtimKelas;
use App\Models\LamtimMasterPembayaran;
use App\Models\LamtimPembayaran;
use App\Models\LamtimRombel;
use App\Models\LamtimSekolah;
use App\Models\LamtimSemester;
use App\Models\LamtimSiswa;
use App\Models\LamtimSiswaRombel;
use App\Models\LamtimTagihan;
use App\Models\LamtimTahunAjaran;
use App\Models\LamtimTipePembayaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetDataService
{
    /**
     * Kategori yang bisa direset, dalam urutan hapus yang aman terhadap FK
     * (child dulu). Model dengan FK cascade (tagihan -> invoice -> pembayaran,
     * master_pembayaran -> tagihan, siswa -> profil/riwayat rombel/tagihan/
     * invoice/pembayaran) akan ikut menghapus turunannya lewat constraint
     * database, bukan lewat kode di sini.
     */
    public const CATEGORY_MAP = [
        'pembayaran' => [
            'model' => LamtimPembayaran::class,
            'label' => 'Pembayaran',
            'tags' => ['pembayaran', 'invoice', 'tagihan', 'dashboard'],
        ],
        'invoice' => [
            'model' => LamtimInvoice::class,
            'label' => 'Invoice',
            'tags' => ['invoice', 'dashboard'],
        ],
        'tagihan' => [
            'model' => LamtimTagihan::class,
            'label' => 'Tagihan',
            'tags' => ['tagihan', 'dashboard'],
        ],
        'master_pembayaran' => [
            'model' => LamtimMasterPembayaran::class,
            'label' => 'Master Pembayaran',
            'tags' => ['master_pembayaran', 'tagihan'],
        ],
        'siswa_rombel' => [
            'model' => LamtimSiswaRombel::class,
            'label' => 'Riwayat Penempatan Rombel Siswa',
            'tags' => ['siswa', 'rombel'],
        ],
        'siswa' => [
            'model' => LamtimSiswa::class,
            'label' => 'Siswa (termasuk Alumni)',
            'tags' => ['siswa', 'dashboard'],
        ],
        'rombel' => [
            'model' => LamtimRombel::class,
            'label' => 'Rombel',
            'tags' => ['rombel', 'siswa'],
        ],
        'jurusan' => [
            'model' => LamtimJurusan::class,
            'label' => 'Jurusan',
            'tags' => ['jurusan', 'rombel', 'siswa'],
        ],
        'kelas' => [
            'model' => LamtimKelas::class,
            'label' => 'Kelas',
            'tags' => ['kelas', 'rombel', 'siswa'],
        ],
        'sekolah' => [
            'model' => LamtimSekolah::class,
            'label' => 'Sekolah',
            'tags' => ['sekolah', 'jurusan', 'rombel', 'siswa'],
        ],
        'tahun_ajaran' => [
            'model' => LamtimTahunAjaran::class,
            'label' => 'Tahun Ajaran',
            'tags' => ['tahun_ajaran'],
        ],
        'semester' => [
            'model' => LamtimSemester::class,
            'label' => 'Semester',
            'tags' => ['semester'],
        ],
        'jenis_pembayaran' => [
            'model' => LamtimJenisPembayaran::class,
            'label' => 'Jenis Pembayaran',
            'tags' => ['jenis_pembayaran'],
        ],
        'kategori_pembayaran' => [
            'model' => LamtimKategoriPembayaran::class,
            'label' => 'Kategori Pembayaran',
            'tags' => ['kategori_pembayaran'],
        ],
        'tipe_pembayaran' => [
            'model' => LamtimTipePembayaran::class,
            'label' => 'Tipe Pembayaran',
            'tags' => ['tipe_pembayaran'],
        ],
    ];

    /**
     * Daftar kategori beserta jumlah baris saat ini, untuk ditampilkan
     * di halaman Reset Data sebelum admin memilih & submit.
     */
    public function getCategories(): array
    {
        $categories = [];

        foreach (self::CATEGORY_MAP as $key => $meta) {
            $categories[] = [
                'key' => $key,
                'label' => $meta['label'],
                'count' => $meta['model']::count(),
            ];
        }

        return $categories;
    }

    /**
     * Hapus permanen seluruh baris pada kategori yang dipilih.
     *
     * @param array<int, string> $selectedKeys
     * @return array<string, int> jumlah baris yang dihapus per kategori
     */
    public function reset(array $selectedKeys): array
    {
        $selectedKeys = array_values(array_intersect($selectedKeys, array_keys(self::CATEGORY_MAP)));

        $results = [];
        $touchedTags = ['dashboard'];

        DB::transaction(function () use ($selectedKeys, &$results, &$touchedTags) {
            foreach (self::CATEGORY_MAP as $key => $meta) {
                if (!in_array($key, $selectedKeys, true)) {
                    continue;
                }

                $model = $meta['model'];
                $results[$key] = $model::count();
                $model::query()->delete();
                $touchedTags = array_merge($touchedTags, $meta['tags']);
            }
        });

        CacheHelper::flushTags(array_unique($touchedTags));

        Log::warning('ResetData: bulk reset executed', [
            'user_id' => Auth::id(),
            'categories' => $selectedKeys,
            'results' => $results,
        ]);

        return $results;
    }
}
