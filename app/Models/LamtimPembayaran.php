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
        'sync_status',
        'sync_at',
        'sync_error',
    ];

    protected $casts = [
        'tanggalBayar' => 'date',
        'verifiedAt' => 'datetime',
        'nominalBayar' => 'decimal:2',
        'status' => 'integer',
        'isVerified' => 'boolean',
        'isActive' => 'boolean',
        'sync_at' => 'datetime',
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

}
