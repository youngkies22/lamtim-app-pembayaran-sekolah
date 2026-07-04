<?php

namespace App\Http\Resources;

use App\Helpers\CacheHelper;
use App\Helpers\FormatHelper;
use App\Models\LamtimSekolah;
use App\Models\LamtimTagihan;
use App\Models\LamtimTahunAjaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Memoization per-request agar tidak query berulang per baris invoice
     * saat dirender lewat InvoiceResource::collection() (list/datatable).
     *
     * @var array<string, array>
     */
    private static array $tagihanBySiswaCache = [];

    /** @var array<string, string> */
    private static array $adminNameCache = [];

    public function toArray(Request $request): array
    {
        // Tahun ajaran aktif & sekolah sama untuk semua baris — cache tag,
        // bukan query ulang per invoice. Di-invalidasi otomatis oleh Observer.
        $tahunAjaranAktif = CacheHelper::remember(['tahun_ajaran'], 'tahun_ajaran_aktif', 3600, fn () => LamtimTahunAjaran::active()->first());
        $sekolah = CacheHelper::remember(['sekolah'], 'sekolah_pertama', 3600, fn () => LamtimSekolah::first());

        // Rombel siswa — pakai relasi yang sudah di-eager-load oleh
        // InvoiceService (siswa.currentRombel.rombel.kelas/jurusan),
        // bukan query baru per invoice.
        $rombelSiswa = $this->siswa?->currentRombel?->rombel;

        // Semua tagihan siswa — memoized per idSiswa dalam satu request,
        // supaya siswa dengan banyak invoice di halaman yang sama tidak
        // memicu query berulang.
        $semuaTagihan = [];
        if ($this->siswa) {
            $idSiswa = $this->siswa->id;
            if (!array_key_exists($idSiswa, self::$tagihanBySiswaCache)) {
                self::$tagihanBySiswaCache[$idSiswa] = LamtimTagihan::where('idSiswa', $idSiswa)
                    ->where('isActive', 1)
                    ->with(['masterPembayaran:id,kode,nama'])
                    ->get()
                    ->map(fn ($t) => [
                        'id' => $t->id,
                        'kodeTagihan' => $t->kodeTagihan,
                        'nama' => $t->masterPembayaran->nama ?? '-',
                        'nominalTagihan' => (float) $t->nominalTagihan,
                        'totalSudahBayar' => (float) $t->totalSudahBayar,
                        'totalSisa' => (float) $t->totalSisa,
                        'status' => $t->status,
                    ])
                    ->all();
            }
            $semuaTagihan = self::$tagihanBySiswaCache[$idSiswa];
        }

        return [
            'id' => $this->id,
            'noInvoice' => $this->noInvoice,
            'kodeInvoice' => $this->kodeInvoice,
            'tanggalInvoice' => $this->tanggalInvoice?->format('Y-m-d'),
            'tanggalInvoiceFormatted' => FormatHelper::date($this->tanggalInvoice),
            'nominalInvoice' => (float) $this->nominalInvoice,
            'nominalInvoiceFormatted' => FormatHelper::currency($this->nominalInvoice),
            'nominalPotongan' => (float) $this->nominalPotongan,
            'nominalPotonganFormatted' => FormatHelper::currency($this->nominalPotongan),
            'nominalBayar' => (float) $this->nominalBayar,
            'nominalBayarFormatted' => FormatHelper::currency($this->nominalBayar),
            'nominalSisa' => (float) $this->nominalSisa,
            'nominalSisaFormatted' => FormatHelper::currency($this->nominalSisa),
            'status' => $this->status,
            'statusLabel' => $this->statusLabel,
            'statusBadge' => FormatHelper::statusBadge($this->status, 'tagihan'),
            'tanggalLunas' => $this->tanggalLunas?->format('Y-m-d'),
            'keterangan' => $this->keterangan,
            'catatan' => $this->catatan,
            'isActive' => (bool) $this->isActive,
            
            // Tahun Ajaran Aktif
            'tahunAjaran' => $tahunAjaranAktif ? [
                'id' => $tahunAjaranAktif->id,
                'nama' => $tahunAjaranAktif->nama,
                'tahun' => $tahunAjaranAktif->tahun,
            ] : null,
            
            // Sekolah
            'sekolah' => $sekolah ? [
                'id' => $sekolah->id,
                'nama' => $sekolah->nama,
                'namaYayasan' => $sekolah->namaYayasan,
                'alamat' => $sekolah->alamat,
                'kota' => $sekolah->kota,
                'telepon' => $sekolah->telepon,
                'email' => $sekolah->email,
                'logo' => $sekolah->logo,
            ] : null,
            
            // Siswa dengan Sekolah dan Rombel
            'siswa' => $this->whenLoaded('siswa', function() use ($rombelSiswa, $sekolah) {
                return [
                    'id' => $this->siswa->id,
                    'nama' => $this->siswa->nama,
                    'nis' => $this->siswa->nis,
                    'nisn' => $this->siswa->nisn,
                    'username' => $this->siswa->username,
                    'kelas' => $rombelSiswa ? ($rombelSiswa->kelas->nama ?? '') . ' ' . ($rombelSiswa->nama ?? '') : '-',
                    'rombel' => $rombelSiswa ? [
                        'id' => $rombelSiswa->id,
                        'nama' => $rombelSiswa->nama,
                        'kelas' => $rombelSiswa->kelas->nama ?? '-',
                        'jurusan' => $rombelSiswa->jurusan->nama ?? '-',
                    ] : null,
                    'sekolah' => $sekolah ? [
                        'id' => $sekolah->id,
                        'nama' => $sekolah->nama,
                        'namaYayasan' => $sekolah->namaYayasan ?? $sekolah->nama,
                        'alamat' => $sekolah->alamat,
                        'kota' => $sekolah->kota,
                        'telepon' => $sekolah->telepon,
                        'email' => $sekolah->email,
                        'logo' => $sekolah->logo,
                    ] : null,
                ];
            }),
            
            // Tagihan saat ini
            'tagihan' => $this->whenLoaded('tagihan', function() {
                return [
                    'id' => $this->tagihan->id,
                    'kodeTagihan' => $this->tagihan->kodeTagihan,
                    'nominalTagihan' => (float) $this->tagihan->nominalTagihan,
                    'totalSudahBayar' => (float) $this->tagihan->totalSudahBayar,
                    'totalSisa' => (float) $this->tagihan->totalSisa,
                    'masterPembayaran' => $this->tagihan->masterPembayaran ? [
                        'id' => $this->tagihan->masterPembayaran->id,
                        'kode' => $this->tagihan->masterPembayaran->kode,
                        'nama' => $this->tagihan->masterPembayaran->nama,
                    ] : null,
                ];
            }),
            
            // Semua tagihan siswa
            'semuaTagihan' => $semuaTagihan,
            
            'masterPembayaran' => $this->whenLoaded('masterPembayaran', function() {
                return [
                    'id' => $this->masterPembayaran->id,
                    'kode' => $this->masterPembayaran->kode,
                    'nama' => $this->masterPembayaran->nama,
                ];
            }),
            'pembayarans' => PembayaranResource::collection($this->whenLoaded('pembayarans')),
            
            // Admin yang memproses — memoized per createdBy dalam satu request
            'admin' => $this->createdBy ? [
                'id' => $this->createdBy,
                'nama' => self::$adminNameCache[$this->createdBy] ??= (User::find($this->createdBy)?->name ?? 'Admin'),
            ] : null,
            
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
