<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LamtimMasterPembayaran extends Model
{
    use HasUuids;

    protected $table = 'lamtim_master_pembayarans';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idTahunAjaran',
        'idSekolah',
        'idJurusan',
        'idRombel',
        'idKelas',
        'kode',
        'nama',
        'slug',
        'jenisPembayaran',
        'kategori',
        'nominal',
        'isCicilan',
        'minCicilan',
        'jumlahBulan',
        'nominalPerBulan',
        'periode',
        'tanggalMulai',
        'tanggalSelesai',
        'isActive',
        'isWajib',
        'keterangan',
        'metadata',
        'createdBy',
        'updatedBy',
        'deletedBy',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'nominalPerBulan' => 'decimal:2',
        'isCicilan' => 'boolean',
        'isActive' => 'boolean',
        'isWajib' => 'boolean',
        'tanggalMulai' => 'date',
        'tanggalSelesai' => 'date',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship dengan tagihan
     */
    public function tagihans(): HasMany
    {
        return $this->hasMany(LamtimTagihan::class, 'idMasterPembayaran', 'id');
    }

    /**
     * Relationship dengan invoices
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(LamtimInvoice::class, 'idMasterPembayaran', 'id');
    }

    /**
     * Scope untuk master aktif
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', 1);
    }

    /**
     * Scope untuk master tidak aktif
     */
    public function scopeInactive($query)
    {
        return $query->where('isActive', 0);
    }

    /**
     * Scope untuk jenis pembayaran tertentu
     */
    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenisPembayaran', $jenis);
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
    }

    /**
     * Restore - set isActive = 1
     */
    public function restore()
    {
        $this->update(['isActive' => 1]);
    }

    /**
     * Generate tagihan untuk siswa berdasarkan master ini
     */
    public function generateTagihanUntukSiswa($siswa, $tahunAjaran = null, $rombel = null, $bulan = null)
    {
        // Cek apakah tagihan sudah ada
        $existing = LamtimTagihan::where('idSiswa', $siswa->id)
            ->where('idMasterPembayaran', $this->id)
            ->where('isActive', 1)
            ->first();

        if ($existing) {
            return $existing; // Return existing tagihan
        }

        // Generate kode tagihan
        $kodeTagihan = $this->generateKodeTagihan($siswa, $bulan);

        // Get snapshot data (data real saat transaksi)
        $currentRombel = $rombel ?? $siswa->currentRombel()->with(['rombel.sekolah', 'rombel.jurusan', 'rombel.kelas'])->first();
        $rombelModel = $currentRombel?->rombel;
        
        // Determine semester based on month
        if ($bulan) {
            $month = (int)explode('-', $bulan)[1];
            $semesterModel = \App\Models\LamtimSemester::getByBulan($month);
        } else {
            $semesterModel = \App\Models\LamtimSemester::getCurrent();
        }
        $idSemester = $semesterModel?->id;

        // Get idTahunAjaran dengan fallback ke tahun ajaran aktif
        $idTahunAjaran = null;
        if ($tahunAjaran && is_object($tahunAjaran)) {
            $idTahunAjaran = $tahunAjaran->id;
        } elseif ($tahunAjaran && is_string($tahunAjaran)) {
            $idTahunAjaran = $tahunAjaran;
        } elseif ($this->idTahunAjaran) {
            $idTahunAjaran = $this->idTahunAjaran;
        } else {
            // Fallback: ambil tahun ajaran aktif
            $tahunAjaranAktif = \App\Models\LamtimTahunAjaran::active()->first();
            if ($tahunAjaranAktif) {
                $idTahunAjaran = $tahunAjaranAktif->id;
            }
        }

        // Buat tagihan baru dengan snapshot data
        return LamtimTagihan::create([
            'idSiswa' => $siswa->id,
            'idMasterPembayaran' => $this->id,
            'idTahunAjaran' => $idTahunAjaran,
            'idRombel' => $currentRombel?->idRombel,
            'idKelas' => $currentRombel?->idKelas, // Snapshot
            'idJurusan' => $rombelModel?->idJurusan, // Snapshot
            'idSekolah' => $rombelModel?->idSekolah, // Snapshot
            'idSemester' => $idSemester, // Snapshot
            'kodeTagihan' => $kodeTagihan,
            'tanggalTagihan' => now(),
            'nominalTagihan' => $this->nominal,
            'totalSisa' => $this->nominal,
            'bulan' => $bulan,
            'namaBulan' => $bulan ? now()->setMonth(explode('-', $bulan)[1])->format('F Y') : null,
            'bulanKe' => $bulan ? (int)explode('-', $bulan)[1] : null,
            'status' => 0, // Belum lunas
            'isActive' => 1,
            'createdBy' => auth()->id(),
        ]);
    }

    /**
     * Generate kode tagihan
     */
    private function generateKodeTagihan($siswa, $bulan = null)
    {
        $prefix = strtoupper($this->jenisPembayaran);
        $tahun = now()->format('Y');
        $nis = $siswa->nis ?? substr($siswa->id, 0, 8);
        $bulanCode = $bulan ? '-' . str_replace('-', '', $bulan) : '';
        
        return "TAG-{$prefix}-{$tahun}-{$nis}{$bulanCode}";
    }
}
