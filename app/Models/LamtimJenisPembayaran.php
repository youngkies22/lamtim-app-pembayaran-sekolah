<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LamtimJenisPembayaran extends Model
{
    use HasUuids;

    protected $table = 'lamtim_jenis_pembayarans';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode',
        'nama',
        'keterangan',
        'isActive',
    ];

    protected $casts = [
        'isActive' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope untuk jenis aktif
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', 1);
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
