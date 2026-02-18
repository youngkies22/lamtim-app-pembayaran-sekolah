<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LamtimSetting extends Model
{
    use HasUuids;

    protected $table = 'lamtim_settings';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'logo_aplikasi',
        'nama_aplikasi',
        'job_sync_external_enabled',
        'job_sync_siswa_enabled',
        'job_push_academic_enabled',
        'job_process_import_enabled',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'job_sync_external_enabled' => 'boolean',
        'job_sync_siswa_enabled' => 'boolean',
        'job_push_academic_enabled' => 'boolean',
        'job_process_import_enabled' => 'boolean',
    ];
}
