<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LamtimRombel extends Model
{
    use HasUuids;

    protected $table = 'lamtim_rombels';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'external_id',
        'idSekolah',
        'idJurusan',
        'idKelas',
        'kode',
        'nama',
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
     * Get the sekolah that owns this rombel
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(LamtimSekolah::class, 'idSekolah', 'id');
    }

    /**
     * Get the jurusan that owns this rombel
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(LamtimJurusan::class, 'idJurusan', 'id');
    }

    /**
     * Get the kelas for this rombel
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(LamtimKelas::class, 'idKelas', 'id');
    }

    /**
     * Relationship dengan siswa (one-to-many via mapping)
     */
    public function siswaRombels(): HasMany
    {
        return $this->hasMany(LamtimSiswaRombel::class, 'idRombel', 'id');
    }

    /**
     * Generate kode rombel automatically
     * Format: tahun + idJurusan + 3 digit random
     */
    public static function generateKode($tahun, $idJurusan): string
    {
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        $jurusanId = substr(str_replace('-', '', $idJurusan), 0, 8);
        return $tahun . $jurusanId . $random;
    }

    /**
     * Scope for active rombel
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', 1);
    }

    /**
     * Scope for inactive rombel
     */
    public function scopeInactive($query)
    {
        return $query->where('isActive', 0);
    }
}
