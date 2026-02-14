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
     * Get semester berdasarkan bulan (1-6 = Ganjil, 7-12 = Genap)
     */
    public static function getByBulan(int $bulan): ?self
    {
        $kode = ($bulan >= 1 && $bulan <= 6) ? 'Ganjil' : 'Genap';
        return static::getByKode($kode);
    }

    /**
     * Get semester saat ini berdasarkan bulan saat ini
     */
    public static function getCurrent(): ?self
    {
        $currentMonth = (int) now()->format('m');
        return static::getByBulan($currentMonth);
    }
}
