<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the external data synchronization API.
    | URL format: {base_url}/{endpoint}/{secret}
    |
    */

    'base_url' => env('URL_API', 'https://budutwj.id/api'),

    'secret' => env('SECRET_API', ''),

    'timeout' => env('EXTERNAL_API_TIMEOUT', 30),

    'retry' => [
        'times' => 3,
        'sleep' => 1000, // milliseconds, will increase exponentially
    ],

    'chunk_size' => env('EXTERNAL_API_CHUNK_SIZE', 100),

    /*
    |--------------------------------------------------------------------------
    | Endpoint Mapping
    |--------------------------------------------------------------------------
    |
    | Maps entity names to their API endpoint paths.
    |
    */

    'endpoints' => [
        'sekolah'      => 'apidataSekolah',
        'kelas'        => 'apidataKelas',
        'jurusan'      => 'apidataJurusan',
        'rombel'       => 'apidataRombel',
        'siswa'        => 'apidataSiswa',
        'tahun_ajaran' => 'apidataTahunAjaran',
        'semester'     => 'apidataSemester',
        
        // Outbound/Push endpoints
        'push_tagihan'    => 'billing/sync-tagihan',
        'push_pembayaran' => 'billing/sync-pembayaran',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Order
    |--------------------------------------------------------------------------
    |
    | Order of synchronization respecting foreign key dependencies.
    | Parent entities must be synced before their children.
    |
    */

    'sync_order' => [
        'tahun_ajaran',
        'semester',
        'sekolah',
        'kelas',
        'jurusan',
        'rombel',
        'siswa',
    ],

];
