<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class LamtimSiswa extends Model
{
    use HasUuids;

    protected $table = 'lamtim_siswas';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'external_id',
        'idAgama',
        'username',
        'nama',
        'password',
        'jsk',
        'nis',
        'nisn',
        'qrcode',
        'fotoOsis',
        'fotoProfile',
        'waSiswa',
        'waOrtu',
        'waWali',
        'isActive',
        'isAlumni',
        'tahunAngkatan',
        'createdBy',
        'updatedBy',
        'deletedBy',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'jsk' => 'integer',
        'isActive' => 'integer',
        'isAlumni' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mutator untuk password - otomatis hash dengan bcrypt
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    /**
     * Accessor untuk jsk (Jenis Kelamin)
     */
    public function getJskAttribute($value)
    {
        return (int) $value;
    }

    /**
     * Get jsk label
     */
    public function getJskLabelAttribute()
    {
        return $this->jsk === 0 ? 'Pria' : 'Laki-laki';
    }

    /**
     * Accessor untuk isActive
     */
    public function getIsActiveAttribute($value)
    {
        return (int) $value;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        if ($this->isAlumni) {
            $base = match($this->isActive) {
                1 => '(Aktif)',
                2 => '(Off)',
                default => ''
            };
            return 'Alumni' . ($base ? " $base" : "");
        }

        return match($this->isActive) {
            1 => 'Aktif',
            0 => 'Dihapus',
            2 => 'Off',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Relationship dengan agama (tanpa foreign key)
     */
    public function agama()
    {
        return $this->belongsTo(LamtimAgama::class, 'idAgama', 'id');
    }

    /**
     * Relationship dengan profile (one-to-one)
     */
    public function profile(): HasOne
    {
        return $this->hasOne(LamtimSiswaProfile::class, 'idSiswa', 'id');
    }

    /**
     * Relationship dengan rombel history (one-to-many)
     */
    public function rombels(): HasMany
    {
        return $this->hasMany(LamtimSiswaRombel::class, 'idSiswa', 'id');
    }

    /**
     * Get current active rombel - relasi Eloquent
     * Menggunakan orderBy untuk mendapatkan rombel terbaru berdasarkan created_at
     */
    public function currentRombel(): HasOne
    {
        return $this->hasOne(LamtimSiswaRombel::class, 'idSiswa', 'id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Relationship dengan tagihan (one-to-many)
     */
    public function tagihans(): HasMany
    {
        return $this->hasMany(LamtimTagihan::class, 'idSiswa', 'id');
    }

    /**
     * Scope untuk siswa aktif
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', 1)->where('isAlumni', 0);
    }

    /**
     * Scope untuk siswa nonaktif
     */
    public function scopeInactive($query)
    {
        return $query->where('isActive', 0);
    }

    /**
     * Scope untuk siswa off
     */
    public function scopeOff($query)
    {
        return $query->where('isActive', 2);
    }

    public function scopeNonAlumni($query)
    {
        return $query->where('isAlumni', 0);
    }

    /**
     * Scope untuk siswa tidak dihapus (aktif atau off)
     */
    public function scopeNotDeleted($query)
    {
        return $query->whereIn('isActive', [1, 2]);
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

    /**
     * Set off - set isActive = 2
     */
    public function setOff()
    {
        $this->update(['isActive' => 2]);
    }

    /**
     * Get formatted current rombel name (with kelas kode)
     */
    public function getFormattedRombel(): ?string
    {
        $current = $this->currentRombel;
        if (!$current || !$current->rombel) {
            return null;
        }

        $rombel = $current->rombel;
        $kelasKode = $rombel->kelas->kode ?? '';
        return trim(($kelasKode ? "$kelasKode " : "") . ($rombel->nama ?? ''));
    }
}
