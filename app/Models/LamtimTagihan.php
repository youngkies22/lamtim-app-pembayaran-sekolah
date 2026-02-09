<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LamtimTagihan extends Model
{
    use HasUuids;

    protected $table = 'lamtim_tagihans';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idSiswa',
        'idMasterPembayaran',
        'idTahunAjaran',
        'idRombel',
        'idKelas',
        'idJurusan',
        'idSekolah',
        'idSemester',
        'kodeTagihan',
        'tanggalTagihan',
        'nominalTagihan',
        'nominalPotongan',
        'totalSudahBayar',
        'totalSisa',
        'bulan',
        'namaBulan',
        'bulanKe',
        'status',
        'tanggalLunas',
        'hariTerlambat',
        'keterangan',
        'catatan',
        'isActive',
        'createdBy',
        'updatedBy',
        'deletedBy',
    ];

    protected $casts = [
        'tanggalTagihan' => 'date',
        'tanggalLunas' => 'date',
        'nominalTagihan' => 'decimal:2',
        'nominalPotongan' => 'decimal:2',
        'totalSudahBayar' => 'decimal:2',
        'totalSisa' => 'decimal:2',
        'status' => 'integer',
        'isActive' => 'boolean',
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
     * Relationship dengan invoices
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(LamtimInvoice::class, 'idTagihan', 'id');
    }

    /**
     * Get invoices yang aktif
     */
    public function invoicesActive(): HasMany
    {
        return $this->invoices()->where('isActive', 1);
    }

    /**
     * Get invoices yang sudah lunas
     */
    public function invoicesLunas(): HasMany
    {
        return $this->invoicesActive()->where('status', 1);
    }

    /**
     * Scope untuk tagihan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', 1);
    }

    /**
     * Scope untuk tagihan belum lunas
     */
    public function scopeBelumLunas($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope untuk tagihan lunas
     */
    public function scopeLunas($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Update status tagihan berdasarkan invoice
     */
    public function updateStatus()
    {
        // Reload relationship
        $this->load('invoicesLunas');
        
        // Hitung total sudah bayar dari semua invoice yang lunas
        $totalSudahBayar = $this->invoicesLunas()->sum('nominalBayar');
        
        // Update tagihan
        $this->totalSudahBayar = $totalSudahBayar;
        $this->totalSisa = $this->nominalTagihan - $this->nominalPotongan - $totalSudahBayar;
        
        // Update status
        if ($this->totalSisa <= 0) {
            $this->status = 1; // Lunas
            if (!$this->tanggalLunas) {
                $this->tanggalLunas = now();
            }
        } elseif ($totalSudahBayar > 0) {
            $this->status = 3; // Sebagian
        } else {
            $this->status = 0; // Belum bayar
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
    }

    /**
     * Restore - set isActive = 1
     */
    public function restore()
    {
        $this->update(['isActive' => 1]);
    }
}
