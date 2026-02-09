<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LamtimTahunAjaran extends Model
{
    use HasUuids;

    protected $table = 'lamtim_tahun_ajarans';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode',
        'tahun',
        'tanggalMulai',
        'tanggalSelesai',
        'isActive',
    ];

    protected $casts = [
        'isActive' => 'boolean',
        'tanggalMulai' => 'date',
        'tanggalSelesai' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get active status attribute
     */
    public function getIsActiveAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Set active status attribute
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['isActive'] = $value ? 1 : 0;
    }

    /**
     * Scope for active tahun ajaran
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', 1);
    }

    /**
     * Scope for inactive tahun ajaran
     */
    public function scopeInactive($query)
    {
        return $query->where('isActive', 0);
    }
}
