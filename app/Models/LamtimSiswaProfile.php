<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LamtimSiswaProfile extends Model
{
    use HasUuids;

    protected $table = 'lamtim_siswa_profiles';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idSiswa',
        'tpl',
        'tgl',
        'kk',
        'nik',
        'tinggiBadan',
        'transport',
        'hobi',
        'jarak',
        'rt',
        'rw',
        'desa',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'pkh',
        'kip',
        'asalSmp',
        'npsnSmp',
        'alamatSmp',
        'ayah',
        'ayahTpl',
        'ayahTgl',
        'ayahNik',
        'ayahPendidikan',
        'ayahAlamat',
        'ibu',
        'ibuTpl',
        'ibuTgl',
        'ibuNik',
        'ibuPendidikan',
        'ibuAlamat',
        'wali',
        'waliTpl',
        'waliTgl',
        'waliNik',
        'waliPendidikan',
        'waliAlamat',
    ];

    protected $casts = [
        'tgl' => 'date',
        'ayahTgl' => 'date',
        'ibuTgl' => 'date',
        'waliTgl' => 'date',
        'pkh' => 'boolean',
        'kip' => 'boolean',
        'tinggiBadan' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship dengan siswa (belongsTo)
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(LamtimSiswa::class, 'idSiswa', 'id');
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->rt ? "RT {$this->rt}" : null,
            $this->rw ? "RW {$this->rw}" : null,
            $this->desa,
            $this->kecamatan,
            $this->kabupaten,
            $this->provinsi,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get age from tanggal lahir
     */
    public function getAgeAttribute()
    {
        if (!$this->tgl) {
            return null;
        }

        return $this->tgl->age;
    }
}
