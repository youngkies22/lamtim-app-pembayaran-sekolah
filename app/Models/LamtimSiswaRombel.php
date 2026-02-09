<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LamtimSiswaRombel extends Model
{
    use HasUuids;

    protected $table = 'lamtim_siswa_rombels';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idSiswa',
        'idRombel',
    ];

    protected $casts = [
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
     * Get sekolah from rombel relationship (virtual attribute)
     */
    public function getSekolahAttribute()
    {
        return $this->rombel?->sekolah;
    }

    /**
     * Get idSekolah from rombel (virtual attribute)
     */
    public function getIdSekolahAttribute()
    {
        return $this->rombel?->idSekolah;
    }

    /**
     * Relationship dengan rombel (belongsTo)
     */
    public function rombel(): BelongsTo
    {
        return $this->belongsTo(LamtimRombel::class, 'idRombel', 'id');
    }

    /**
     * Get jurusan from rombel relationship (virtual attribute)
     */
    public function getJurusanAttribute()
    {
        return $this->rombel?->jurusan;
    }

    /**
     * Get idJurusan from rombel (virtual attribute)
     */
    public function getIdJurusanAttribute()
    {
        return $this->rombel?->idJurusan;
    }

    /**
     * Get kelas from rombel relationship (virtual attribute)
     */
    public function getKelasAttribute()
    {
        return $this->rombel?->kelas;
    }

    /**
     * Get idKelas from rombel (virtual attribute)
     */
    public function getIdKelasAttribute()
    {
        return $this->rombel?->idKelas;
    }

    /**
     * Create siswa rombel assignment
     */
    public static function createAssignment($idSiswa, $idRombel)
    {
        return self::create([
            'idSiswa' => $idSiswa,
            'idRombel' => $idRombel,
        ]);
    }

    /**
     * Get full class info from relations
     */
    public function getFullClassInfoAttribute()
    {
        $parts = array_filter([
            $this->rombel?->nama,
            $this->jurusan?->nama,
            $this->kelas?->kode,
        ]);

        return implode(' - ', $parts);
    }

    /**
     * Get rombel name from relation
     */
    public function getNamaRombelAttribute()
    {
        return $this->rombel?->nama;
    }

    /**
     * Get jurusan name from relation
     */
    public function getNamaJurusanAttribute()
    {
        return $this->jurusan?->nama;
    }

    /**
     * Get kelas code from relation (via rombel)
     */
    public function getKodeKelasAttribute()
    {
        return $this->rombel?->kelas?->kode;
    }
}
