<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LamtimJurusan extends Model
{
    use HasUuids;

    protected $table = 'lamtim_jurusans';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'external_id',
        'idSekolah',
        'kode',
        'nama',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the sekolah that owns this jurusan
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(LamtimSekolah::class, 'idSekolah', 'id');
    }

    /**
     * Get all rombel for this jurusan
     */
    public function rombels(): HasMany
    {
        return $this->hasMany(LamtimRombel::class, 'idJurusan', 'id');
    }
}
