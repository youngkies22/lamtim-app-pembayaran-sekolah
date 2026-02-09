<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LamtimInvoice extends Model
{
    use HasUuids;

    protected $table = 'lamtim_invoices';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idSiswa',
        'idTagihan',
        'idMasterPembayaran',
        'idTahunAjaran',
        'idRombel',
        'idKelas',
        'idJurusan',
        'idSekolah',
        'idSemester',
        'noInvoice',
        'kodeInvoice',
        'tanggalInvoice',
        'nominalInvoice',
        'nominalPotongan',
        'nominalBayar',
        'nominalSisa',
        'status',
        'tanggalLunas',
        'keterangan',
        'catatan',
        'metadata',
        'isActive',
        'createdBy',
        'updatedBy',
        'deletedBy',
    ];

    protected $casts = [
        'tanggalInvoice' => 'date',
        'tanggalLunas' => 'date',
        'nominalInvoice' => 'decimal:2',
        'nominalPotongan' => 'decimal:2',
        'nominalBayar' => 'decimal:2',
        'nominalSisa' => 'decimal:2',
        'status' => 'integer',
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
     * Relationship dengan pembayaran
     */
    public function pembayarans(): HasMany
    {
        return $this->hasMany(LamtimPembayaran::class, 'idInvoice', 'id');
    }

    /**
     * Get pembayaran yang aktif
     */
    public function pembayaransActive(): HasMany
    {
        return $this->pembayarans()->where('isActive', 1);
    }

    /**
     * Get pembayaran yang valid
     */
    public function pembayaransValid(): HasMany
    {
        return $this->pembayaransActive()->where('status', 1);
    }

    /**
     * Scope untuk invoice aktif
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', 1);
    }

    /**
     * Scope untuk invoice lunas
     */
    public function scopeLunas($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Update status invoice berdasarkan pembayaran
     */
    public function updateStatus()
    {
        // Hitung total sudah bayar dari semua pembayaran yang valid
        $totalBayar = $this->pembayaransValid()->sum('nominalBayar');
        
        // Update invoice
        $this->nominalBayar = $totalBayar;
        $this->nominalSisa = $this->nominalInvoice - $this->nominalPotongan - $totalBayar;
        
        // Update status
        if ($this->nominalSisa <= 0) {
            $this->status = 1; // Lunas
            if (!$this->tanggalLunas) {
                $this->tanggalLunas = now();
            }
        } elseif ($totalBayar > 0) {
            $this->status = 3; // Sebagian
        } else {
            $this->status = 0; // Belum bayar
        }
        
        // Update tagihan juga
        if ($this->tagihan) {
            $this->tagihan->updateStatus();
        }
        
        $this->save();
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            0 => 'Belum Lunas',
            1 => 'Lunas',
            3 => 'Sebagian',
            default => 'Tidak Diketahui'
        };
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
        
        // Update tagihan juga
        if ($this->tagihan) {
            $this->tagihan->updateStatus();
        }
    }

    /**
     * Restore - set isActive = 1
     */
    public function restore()
    {
        $this->update(['isActive' => 1]);
        
        // Update tagihan juga
        if ($this->tagihan) {
            $this->tagihan->updateStatus();
        }
    }
}
