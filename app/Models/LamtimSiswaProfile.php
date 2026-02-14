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
        'tempatLahir',
        'tanggalLahir',
        'alamat',
        'kota',
        'provinsi',
        'kodePos',
        'telepon',
        'email',
        'namaAyah',
        'pekerjaanAyah',
        'teleponAyah',
        'namaIbu',
        'pekerjaanIbu',
        'teleponIbu',
        'namaWali',
        'hubunganWali',
        'pekerjaanWali',
        'teleponWali',
        'alamatWali',
        'asalSekolah',
        'alamatSekolahAsal',
        'noIjazah',
        'noSKHUN',
    ];

    protected $casts = [
        'tanggalLahir' => 'date',
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
            $this->alamat,
            $this->kota,
            $this->provinsi,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get age from tanggal lahir
     */
    public function getAgeAttribute()
    {
        if (!$this->tanggalLahir) {
            return null;
        }

        return $this->tanggalLahir->age;
    }
}
