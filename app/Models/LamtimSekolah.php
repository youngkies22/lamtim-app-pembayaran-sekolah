<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LamtimSekolah extends Model
{
    use HasUuids;

    protected $table = 'lamtim_sekolahs';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'npsn',
        'kode',
        'nama',
        'namaYayasan',
        'alamat',
        'kota',
        'provinsi',
        'akreditasi',
        'telepon',
        'email',
        'website',
        'logo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all jurusan for this sekolah
     */
    public function jurusans(): HasMany
    {
        return $this->hasMany(LamtimJurusan::class, 'idSekolah', 'id');
    }

    /**
     * Get all rombel for this sekolah
     */
    public function rombels(): HasMany
    {
        return $this->hasMany(LamtimRombel::class, 'idSekolah', 'id');
    }
}
