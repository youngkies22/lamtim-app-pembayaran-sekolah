<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LamtimPembayaran extends Model
{
    use HasUuids;

    protected $table = 'lamtim_pembayarans';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idSiswa',
        'idInvoice',
        'idTagihan',
        'idMasterPembayaran',
        'idTahunAjaran',
        'idRombel',
        'idKelas',
        'idJurusan',
        'idSekolah',
        'idSemester',
        'kodePembayaran',
        'noReferensi',
        'tanggalBayar',
        'nominalBayar',
        'metodeBayar',
        'channelBayar',
        'namaChannel',
        'buktiBayar',
        'keterangan',
        'status',
        'isVerified',
        'verifiedBy',
        'verifiedAt',
        'alasanBatal',
        'metadata',
        'isActive',
        'createdBy',
        'updatedBy',
        'deletedBy',
    ];

    protected $casts = [
        'tanggalBayar' => 'date',
        'verifiedAt' => 'datetime',
        'nominalBayar' => 'decimal:2',
        'status' => 'integer',
        'isVerified' => 'boolean',
        'isActive' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship dengan siswa
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(LamtimSiswa::class, 'idSiswa', 'id');
    }

    /**
     * Relationship dengan invoice
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(LamtimInvoice::class, 'idInvoice', 'id');
    }

    /**
     * Relationship dengan tagihan
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(LamtimTagihan::class, 'idTagihan', 'id');
    }

    /**
     * Relationship dengan master pembayaran
     */
    public function masterPembayaran(): BelongsTo
    {
        return $this->belongsTo(LamtimMasterPembayaran::class, 'idMasterPembayaran', 'id');
    }

    /**
     * Relationship dengan semester
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(LamtimSemester::class, 'idSemester', 'id');
    }

    /**
     * Scope untuk pembayaran aktif
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', 1);
    }

    /**
     * Scope untuk pembayaran valid
     */
    public function scopeValid($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope untuk pembayaran verified
     */
    public function scopeVerified($query)
    {
        return $query->where('isVerified', 1);
    }

    /**
     * Verifikasi pembayaran
     */
    public function verify($verifiedBy = null)
    {
        $this->update([
            'isVerified' => 1,
            'verifiedBy' => $verifiedBy ?? auth()->id(),
            'verifiedAt' => now(),
        ]);
        
        // Update invoice status
        if ($this->invoice) {
            $this->invoice->updateStatus();
        }
    }

    /**
     * Batalkan pembayaran
     */
    public function cancel($alasan = null, $deletedBy = null)
    {
        $this->update([
            'status' => 0,
            'alasanBatal' => $alasan,
            'deletedBy' => $deletedBy,
        ]);
        
        // Update invoice status
        if ($this->invoice) {
            $this->invoice->updateStatus();
        }
    }

    /**
     * Soft delete - set isActive = 0
     */
    public function softDelete($deletedBy = null)
    {
        $this->update([
            'isActive' => 0,
            'deletedBy' => $deletedBy,
        ]);
        
        // Hapus invoice juga (Cascade Soft Delete)
        if ($this->invoice) {
            $this->invoice->softDelete($deletedBy);
        }
    }

    /**
     * Restore - set isActive = 1
     */
    public function restore()
    {
        $this->update(['isActive' => 1]);
        
        // Restore invoice juga
        if ($this->invoice) {
            $this->invoice->restore();
        }
    }

    /**
     * Static method: Proses pembayaran (buat invoice + input pembayaran)
     * INVOICE DIBUAT SAAT ADA PEMBAYARAN
     */
    public static function prosesPembayaran(
        $idSiswa,
        $idMasterPembayaran,
        $nominalBayar,
        $metodeBayar = 'Tunai',
        $channelBayar = null,
        $namaChannel = null,
        $buktiBayar = null,
        $keterangan = null
    ) {
        // 1. Cari atau buat tagihan
        $tagihan = LamtimTagihan::where('idSiswa', $idSiswa)
            ->where('idMasterPembayaran', $idMasterPembayaran)
            ->where('isActive', 1)
            ->first();
        
        if (!$tagihan) {
            // Generate tagihan jika belum ada
            $master = LamtimMasterPembayaran::find($idMasterPembayaran);
            $siswa = LamtimSiswa::find($idSiswa);
            
            // Get tahun ajaran aktif sebagai fallback
            $tahunAjaranAktif = \App\Models\LamtimTahunAjaran::active()->first();
            $tagihan = $master->generateTagihanUntukSiswa($siswa, $tahunAjaranAktif);
        }
        
        // 2. Validasi nominal
        if ($nominalBayar > $tagihan->totalSisa) {
            throw new \Exception('Nominal pembayaran melebihi sisa tagihan');
        }
        
        // Validasi minimum cicilan
        if ($tagihan->masterPembayaran->isCicilan && $tagihan->masterPembayaran->minCicilan) {
            if ($nominalBayar < $tagihan->masterPembayaran->minCicilan) {
                throw new \Exception('Minimum cicilan adalah ' . number_format($tagihan->masterPembayaran->minCicilan, 0, ',', '.'));
            }
        }
        
        // Get snapshot data from tagihan (data real saat transaksi)
        $currentMonth = (int)now()->format('m');
        $semester = $currentMonth >= 1 && $currentMonth <= 6 ? 'Ganjil' : 'Genap';

        // 3. BUAT INVOICE (saat ada pembayaran) dengan snapshot data
        $invoice = LamtimInvoice::create([
            'idSiswa' => $idSiswa,
            'idTagihan' => $tagihan->id,
            'idMasterPembayaran' => $idMasterPembayaran,
            'idTahunAjaran' => $tagihan->idTahunAjaran,
            'idRombel' => $tagihan->idRombel,
            'idKelas' => $tagihan->idKelas, // Snapshot from tagihan
            'idJurusan' => $tagihan->idJurusan, // Snapshot from tagihan
            'idSekolah' => $tagihan->idSekolah, // Snapshot from tagihan
            'semester' => $tagihan->semester ?? $semester, // Snapshot from tagihan or current
            'noInvoice' => self::generateNoInvoice($idMasterPembayaran, $idSiswa),
            'kodeInvoice' => self::generateKodeInvoice($idMasterPembayaran, $idSiswa),
            'tanggalInvoice' => now(),
            'nominalInvoice' => $nominalBayar, // Nominal invoice = nominal bayar
            'nominalSisa' => $nominalBayar, // Awalnya sisa = nominal invoice
            'status' => 0, // Belum lunas
            'isActive' => 1,
            'createdBy' => auth()->id(),
        ]);
        
        // 4. INPUT PEMBAYARAN ke invoice dengan snapshot data
        $pembayaran = self::create([
            'idSiswa' => $idSiswa,
            'idInvoice' => $invoice->id,
            'idTagihan' => $tagihan->id,
            'idMasterPembayaran' => $idMasterPembayaran,
            'idTahunAjaran' => $tagihan->idTahunAjaran, // Snapshot
            'idRombel' => $tagihan->idRombel, // Snapshot
            'idKelas' => $tagihan->idKelas, // Snapshot
            'idJurusan' => $tagihan->idJurusan, // Snapshot
            'idSekolah' => $tagihan->idSekolah, // Snapshot
            'semester' => $tagihan->semester ?? $semester, // Snapshot
            'kodePembayaran' => self::generateKodePembayaran(),
            'tanggalBayar' => now(),
            'nominalBayar' => $nominalBayar,
            'metodeBayar' => $metodeBayar,
            'channelBayar' => $channelBayar,
            'namaChannel' => $namaChannel,
            'buktiBayar' => $buktiBayar,
            'keterangan' => $keterangan,
            'status' => 1, // Valid
            'isVerified' => 0, // Belum diverifikasi
            'isActive' => 1,
            'createdBy' => auth()->id(),
        ]);
        
        // 5. Update invoice status
        $invoice->updateStatus();
        
        return [
            'invoice' => $invoice,
            'pembayaran' => $pembayaran,
            'tagihan' => $tagihan->fresh(),
        ];
    }

    /**
     * Generate nomor invoice
     */
    private static function generateNoInvoice($idMasterPembayaran, $idSiswa)
    {
        $master = LamtimMasterPembayaran::find($idMasterPembayaran);
        $siswa = LamtimSiswa::find($idSiswa);
        $prefix = strtoupper($master->jenisPembayaran);
        $tahun = now()->format('Y');
        $nis = $siswa->nis ?? substr($siswa->id, 0, 8);
        $count = LamtimInvoice::where('idMasterPembayaran', $idMasterPembayaran)
            ->whereYear('created_at', $tahun)
            ->count() + 1;
        
        return "INV-{$prefix}-{$tahun}-{$nis}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate kode invoice
     */
    private static function generateKodeInvoice($idMasterPembayaran, $idSiswa)
    {
        $master = LamtimMasterPembayaran::find($idMasterPembayaran);
        $siswa = LamtimSiswa::find($idSiswa);
        $prefix = strtoupper($master->jenisPembayaran);
        $tahun = now()->format('Y');
        $nis = $siswa->nis ?? substr($siswa->id, 0, 8);
        $timestamp = now()->format('YmdHis');
        
        return "{$prefix}-{$tahun}-{$nis}-{$timestamp}";
    }

    /**
     * Generate kode pembayaran
     */
    private static function generateKodePembayaran()
    {
        $tahun = now()->format('Y');
        $count = self::whereYear('created_at', $tahun)->count() + 1;
        
        return 'PAY-' . now()->format('YmdHis') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
