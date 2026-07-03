<?php

namespace App\Observers;

use App\Helpers\CacheHelper;
use App\Models\LamtimAgama;
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
use App\Models\LamtimSetting;
use App\Models\LamtimSiswa;
use App\Models\LamtimTagihan;
use App\Models\LamtimTahunAjaran;
use App\Models\LamtimTipePembayaran;
use Illuminate\Database\Eloquent\Model;

/**
 * Invalidasi cache Redis per-tag saat model berubah.
 *
 * Setiap model dipetakan ke tag utamanya + tag terkait yang ikut basi.
 * Tag per-record ('{tag}:{uuid}') juga di-flush agar cache detail ikut bersih.
 */
class MasterDataObserver
{
    /**
     * Peta model => [tag utama, ...tag terkait yang ikut basi].
     *
     * @var array<class-string, array<int, string>>
     */
    protected const TAG_MAP = [
        LamtimJurusan::class => ['jurusan', 'rombel', 'siswa'],
        LamtimKelas::class => ['kelas', 'rombel', 'siswa'],
        LamtimRombel::class => ['rombel', 'siswa'],
        LamtimAgama::class => ['agama'],
        LamtimSiswa::class => ['siswa', 'dashboard'],
        LamtimSekolah::class => ['sekolah', 'jurusan', 'rombel', 'siswa'],
        LamtimSemester::class => ['semester'],
        LamtimTahunAjaran::class => ['tahun_ajaran'],
        LamtimMasterPembayaran::class => ['master_pembayaran', 'tagihan'],
        LamtimTagihan::class => ['tagihan', 'dashboard'],
        LamtimInvoice::class => ['invoice', 'dashboard'],
        LamtimPembayaran::class => ['pembayaran', 'invoice', 'tagihan', 'dashboard'],
        LamtimTipePembayaran::class => ['tipe_pembayaran'],
        LamtimJenisPembayaran::class => ['jenis_pembayaran'],
        LamtimKategoriPembayaran::class => ['kategori_pembayaran'],
        LamtimSetting::class => ['settings'],
    ];

    public function created(Model $model): void
    {
        $this->flushFor($model);
    }

    public function updated(Model $model): void
    {
        $this->flushFor($model);
    }

    public function deleted(Model $model): void
    {
        $this->flushFor($model);
    }

    public function restored(Model $model): void
    {
        $this->flushFor($model);
    }

    public function forceDeleted(Model $model): void
    {
        $this->flushFor($model);
    }

    /**
     * Flush tag utama, tag terkait, dan tag per-record milik model.
     */
    protected function flushFor(Model $model): void
    {
        $tags = static::TAG_MAP[get_class($model)] ?? null;

        if (!$tags) {
            return;
        }

        $primaryTag = $tags[0];

        CacheHelper::flushTags([
            ...$tags,
            "{$primaryTag}:{$model->getKey()}",
        ]);
    }
}
