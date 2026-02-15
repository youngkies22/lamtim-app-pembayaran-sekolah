<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LamtimSemester extends Model
{
    use HasUuids;

    protected $table = 'lamtim_semesters';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'external_id',
        'kode',
        'nama',
        'namaSingkat',
        'isActive',
    ];

    protected $casts = [
        'isActive' => 'boolean',
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
     * Scope for active semester
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', 1);
    }

    /**
     * Scope for inactive semester
     */
    public function scopeInactive($query)
    {
        return $query->where('isActive', 0);
    }

    /**
     * Get semester by kode
     */
    public static function getByKode(string $kode): ?self
    {
        return static::where('kode', $kode)->first();
    }

    /**
     * Get semester berdasarkan bulan
     * Jan-Jun (1-6) = Genap
     * Jul-Dec (7-12) = Ganjil
     */
    public static function getByBulan(int $bulan): ?self
    {
        $search = ($bulan >= 1 && $bulan <= 6) ? 'GENAP' : 'GANJIL';
        
        // Coba cari yang aktif dulu yang mengandung kata tersebut
        $activeMatching = static::active()
            ->where(function($q) use ($search) {
                $q->where('kode', 'LIKE', "%{$search}%")
                  ->orWhere('nama', 'LIKE', "%{$search}%");
            })->first();

        if ($activeMatching) {
            return $activeMatching;
        }

        // Fallback: cari yang mana saja yang matching
        return static::where('kode', 'LIKE', "%{$search}%")
            ->orWhere('nama', 'LIKE', "%{$search}%")
            ->first();
    }

    /**
     * Get semester saat ini berdasarkan bulan saat ini
     */
    public static function getCurrent(): ?self
    {
        // Prioritas: Ambil yang ditandai isActive = 1
        $active = static::active()->first();
        if ($active) {
            return $active;
        }

        $currentMonth = (int) now()->format('m');
        return static::getByBulan($currentMonth);
    }
}
