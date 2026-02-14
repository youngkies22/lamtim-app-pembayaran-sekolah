<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

use App\Models\LamtimAgama;
use App\Models\LamtimJurusan;
use App\Models\LamtimKelas;
use App\Models\LamtimRombel;
use App\Models\LamtimSekolah;
use App\Models\LamtimSemester;
use App\Models\LamtimSiswa;
use App\Models\LamtimSiswaProfile;
use App\Models\LamtimSiswaRombel;
use App\Models\LamtimTahunAjaran;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates synchronization of external API data into local DB.
 *
 * Uses `kode` fields as the deduplication key (not the API's integer IDs).
 * Caches API integer IDs → local UUIDs for FK resolution.
 */
class ExternalSyncService
{
    protected ExternalApiClient $apiClient;

    /** API/Kode ref → local UUID maps for FK resolution. */
    protected array $idMaps = [];

    /** Sync results per entity. */
    protected array $results = [];

    public function __construct(ExternalApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    // =========================================================================
    // NEW: Frontend-driven sync methods (controller calls these directly)
    // =========================================================================

    /**
     * Sync a single NON-SISWA entity synchronously. Called directly from controller.
     */
    public function syncSingleEntity(string $entity): array
    {
        $this->preloadIdMaps();

        $data = $this->apiClient->fetch($entity);

        if (empty($data)) {
            return [
                'status' => 'completed',
                'message' => 'Tidak ada data dari API',
                'inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => [],
            ];
        }

        $stats = ['inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];

        DB::beginTransaction();
        try {
            foreach ($data as $record) {
                try {
                    $result = $this->syncRecord($entity, $record);
                    $stats[$result['action']]++;
                } catch (\Exception $e) {
                    $stats['failed']++;
                    $stats['errors'][] = $e->getMessage();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $stats['failed'] = count($data);
            $stats['errors'][] = 'Transaction error: ' . $e->getMessage();
        }

        $stats['status'] = $stats['failed'] > 0 ? 'completed_with_errors' : 'completed';
        $stats['message'] = "Sync selesai: {$stats['inserted']} tambah, {$stats['updated']} update, {$stats['failed']} gagal";

        $this->results[$entity] = $stats;
        $this->saveLastSyncTime();

        return $stats;
    }

    /**
     * Download siswa data from API to JSON file. Returns total count.
     */
    public function downloadSiswaToFile(): array
    {
        $data = $this->apiClient->fetch('siswa');

        if (empty($data)) {
            return ['total' => 0, 'message' => 'Tidak ada data siswa dari API'];
        }

        $dir = storage_path('sync');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filePath = "{$dir}/siswa.json";
        File::put($filePath, json_encode($data));

        return ['total' => count($data)];
    }

    /**
     * Process a chunk of siswa from the downloaded JSON file.
     */
    public function processSiswaChunk(int $offset, int $limit): array
    {
        $this->preloadIdMaps();

        $filePath = storage_path('sync/siswa.json');
        if (!File::exists($filePath)) {
            throw new \Exception('File siswa.json tidak ditemukan. Download terlebih dahulu.');
        }

        $jsonContent = File::get($filePath);
        $allData = json_decode($jsonContent, true);

        if (empty($allData)) {
            throw new \Exception('File siswa.json kosong.');
        }

        $total = count($allData);
        $chunk = array_slice($allData, $offset, $limit);

        $stats = ['inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => [], 'processed' => 0];

        // Process each record independently (no shared transaction)
        // so one failure doesn't cascade to subsequent records
        foreach ($chunk as $record) {
            try {
                DB::beginTransaction();
                $result = $this->syncRecord('siswa', $record);
                $stats[$result['action']]++;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $stats['failed']++;
                $stats['errors'][] = '[siswa] ' . $e->getMessage();
            }
            $stats['processed']++;
        }

        $stats['total'] = $total;
        $stats['offset'] = $offset;
        $stats['done'] = ($offset + count($chunk)) >= $total;

        return $stats;
    }

    /**
     * Cleanup temporary siswa JSON file.
     */
    public function cleanupSiswaFile(): void
    {
        $filePath = storage_path('sync/siswa.json');
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }

    public function syncSiswaBackground(): array
    {
        $syncOrder = config('external_api.sync_order', []);
        $siswaIndex = array_search('siswa', $syncOrder) !== false ? array_search('siswa', $syncOrder) + 1 : count($syncOrder);
        $totalEntities = count($syncOrder);

        // Preload ID maps for the job context
        $this->preloadIdMaps();

        $result = $this->syncEntity('siswa', $siswaIndex, $totalEntities);
        
        // Save results and clear real-time progress
        $this->results['siswa'] = $result;
        $this->saveLastSyncTime();
        
        return $result;
    }

    // =========================================================================
    // CLI-compatible sync (used by artisan command)
    // =========================================================================

    /**
     * Run sync for all or a specific entity (CLI).
     */
    public function sync(?string $entity = null, ?callable $onProgress = null): array
    {
        $this->results = [];
        $syncOrder = config('external_api.sync_order', []);
        $entities = $entity ? [$entity] : $syncOrder;

        // Pre-load existing ID maps for FK resolution
        $this->preloadIdMaps();

        // Clear previous progress
        $this->clearProgress();

        $totalEntities = count($entities);
        $currentEntityIndex = 0;

        foreach ($entities as $entityName) {
            $currentEntityIndex++;
            if (!in_array($entityName, $syncOrder)) {
                $this->results[$entityName] = [
                    'status' => 'skipped', 'message' => "Entity '{$entityName}' tidak valid",
                    'inserted' => 0, 'updated' => 0, 'soft_deleted' => 0, 'failed' => 0, 'errors' => [],
                ];
                continue;
            }

            try {
                $this->results[$entityName] = $this->syncEntity($entityName, $currentEntityIndex, $totalEntities, $onProgress);
            } catch (\Exception $e) {
                Log::error("Sync failed for {$entityName}", ['error' => $e->getMessage()]);
                $this->results[$entityName] = [
                    'status' => 'failed', 'message' => $e->getMessage(),
                    'inserted' => 0, 'updated' => 0, 'soft_deleted' => 0, 'failed' => 1, 'errors' => [$e->getMessage()],
                ];
            }
        }

        $this->saveLastSyncTime();
        return $this->results;
    }

    /**
     * Pre-load ID maps from existing synced records.
     * This allows FK resolution even when syncing a single child entity.
     */
    protected function preloadIdMaps(): void
    {
        // Sekolah: kode → local UUID
        LamtimSekolah::all()->each(function ($m) {
            $this->idMaps['sekolah'][$m->kode] = $m->id;
        });

        // Jurusan: kode → local UUID
        LamtimJurusan::all()->each(function ($m) {
            $this->idMaps['jurusan'][$m->kode] = $m->id;
        });

        // Kelas: kode → local UUID
        LamtimKelas::all()->each(function ($m) {
            $this->idMaps['kelas'][$m->kode] = $m->id;
        });

        // Rombel: kode → local UUID
        LamtimRombel::all()->each(function ($m) {
            $this->idMaps['rombel'][$m->kode] = $m->id;
        });

        // Tahun Ajaran: kode → local UUID
        LamtimTahunAjaran::all()->each(function ($m) {
            $this->idMaps['tahun_ajaran'][$m->kode] = $m->id;
        });

        // Semester: kode → local UUID
        LamtimSemester::all()->each(function ($m) {
            $this->idMaps['semester'][$m->kode] = $m->id;
        });

        // Agama: nama → local UUID
        LamtimAgama::all()->each(function ($m) {
            $this->idMaps['agama'][strtoupper($m->nama)] = $m->id;
            $this->idMaps['agama'][strtoupper($m->kode)] = $m->id;
        });
    }

    /**
     * Sync a single entity type.
     */
    protected function syncEntity(string $entity, int $entityIndex = 1, int $totalEntities = 1, ?callable $onProgress = null): array
    {

        set_time_limit(0);

        $stats = ['inserted' => 0, 'updated' => 0, 'soft_deleted' => 0, 'failed' => 0, 'errors' => []];

        try {
            // Stage 1: Download
            $this->updateProgress($entity, 0, 0, 'downloading', $entityIndex, $totalEntities);
            if ($onProgress) $onProgress($entity, 0, 0);

            $filePath = $this->downloadEntity($entity);
            
            if (!$filePath || !File::exists($filePath)) {
                return [
                    'status' => 'completed', 'message' => 'Tidak ada data dari API',
                    'inserted' => 0, 'updated' => 0, 'soft_deleted' => 0, 'failed' => 0, 'errors' => [],
                ];
            }

            // Stage 2: Process
            $jsonContent = File::get($filePath);
            $apiData = json_decode($jsonContent, true);
            
            if (empty($apiData)) {
                $this->cleanupSyncFile($entity);
                return [
                    'status' => 'completed', 'message' => 'File data kosong',
                    'inserted' => 0, 'updated' => 0, 'soft_deleted' => 0, 'failed' => 0, 'errors' => [],
                ];
            }

            $totalRecords = count($apiData);
            $processedCount = 0;
            $chunkSize = config('external_api.chunk_size', 100);
            $chunks = array_chunk($apiData, $chunkSize);

            foreach ($chunks as $chunk) {
                try {
                    DB::beginTransaction();

                    foreach ($chunk as $record) {
                        try {
                            $result = $this->syncRecord($entity, $record);
                            $stats[$result['action']]++;
                        } catch (\Exception $e) {
                            $stats['failed']++;
                            $stats['errors'][] = "[{$entity}] " . $e->getMessage();
                            Log::warning("Sync record failed", [
                                'entity' => $entity,
                                'error' => $e->getMessage(),
                            ]);
                        }
                        $processedCount++;
                        $this->updateProgress($entity, $processedCount, $totalRecords, 'processing', $entityIndex, $totalEntities, [
                            'inserted' => $stats['inserted'],
                            'updated'  => $stats['updated'],
                            'failed'   => $stats['failed'],
                        ]);
                        if ($onProgress) {
                            $onProgress($entity, $processedCount, $totalRecords);
                        }
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $stats['failed'] += count($chunk);
                    $stats['errors'][] = "Chunk error: " . $e->getMessage();
                    Log::error("Chunk transaction failed for {$entity}", ['error' => $e->getMessage()]);
                }
            }

            // Stage 3: Cleanup
            $this->cleanupSyncFile($entity);

        } catch (\Exception $e) {
            Log::error("Sync process failed for {$entity}", ['error' => $e->getMessage()]);
            $stats['failed']++;
            $stats['errors'][] = "System error: " . $e->getMessage();
            $this->cleanupSyncFile($entity);
        }

        $stats['status'] = $stats['failed'] > 0 ? 'completed_with_errors' : 'completed';
        $stats['message'] = "Sync selesai: {$stats['inserted']} tambah, {$stats['updated']} update, {$stats['failed']} gagal";


        if ($onProgress) $onProgress($entity, $totalRecords ?? 0, $totalRecords ?? 0);
        return $stats;
    }

    protected function syncRecord(string $entity, array $record): array
    {
        return match ($entity) {
            'tahun_ajaran' => $this->syncTahunAjaran($record),
            'semester'     => $this->syncSemester($record),
            'sekolah'      => $this->syncSekolah($record),
            'kelas'        => $this->syncKelas($record),
            'jurusan'      => $this->syncJurusan($record),
            'rombel'       => $this->syncRombel($record),
            'siswa'        => $this->syncSiswa($record),
            default        => throw new \InvalidArgumentException("Unknown entity: {$entity}"),
        };
    }

    // =========================================================================
    // Entity sync methods — using actual API field names
    // =========================================================================

    /**
     * API: {tajrId, tajrKode, tajrNama, tajrDescription}
     * Dedup: tajrKode → kode
     */
    protected function syncTahunAjaran(array $r): array
    {
        $kode = $r['tajrKode'] ?? null;
        if (!$kode) {
            throw new \Exception('tahun_ajaran: tajrKode kosong');
        }

        $data = [
            'kode'  => $kode,
            'tahun' => $r['tajrNama'] ?? $kode,
        ];

        $existing = LamtimTahunAjaran::where('kode', $kode)->first();

        if ($existing) {
            $existing->update(array_merge($data, ['external_id' => (string) ($r['tajrId'] ?? $kode)]));
            $this->cacheId('tahun_ajaran', (string) ($r['tajrId'] ?? $kode), $existing->id);
            return ['action' => 'updated'];
        }

        $data['external_id'] = (string) ($r['tajrId'] ?? $kode);
        $model = LamtimTahunAjaran::create($data);
        $this->cacheId('tahun_ajaran', (string) ($r['tajrId'] ?? $kode), $model->id);
        return ['action' => 'inserted'];
    }

    /**
     * API: {smId, smTajrId, smSklId, smKode, smNama}
     * Dedup: smKode → kode
     */
    protected function syncSemester(array $r): array
    {
        $kode = $r['smKode'] ?? null;
        if (!$kode) {
            throw new \Exception('semester: smKode kosong');
        }

        $data = [
            'kode'        => $kode,
            'nama'        => $r['smNama'] ?? $kode,
            'external_id' => (string) ($r['smId'] ?? $kode),
        ];

        $existing = LamtimSemester::where('kode', $kode)->first();

        if ($existing) {
            $existing->update($data);
            $this->cacheId('semester', (string) ($r['smId'] ?? $kode), $existing->id);
            return ['action' => 'updated'];
        }

        $model = LamtimSemester::create($data);
        $this->cacheId('semester', (string) ($r['smId'] ?? $kode), $model->id);
        return ['action' => 'inserted'];
    }

    /**
     * API: {sklNpsn, sklKode, sklNama, sklAlamat, sklSlag, profile_sekolah{prsklProvinsi, prsklKabupaten, prsklKecamatan}}
     * Dedup: sklKode → kode
     * FK cache key: sklNpsn (used by semester.smSklId and siswa.ssaSklId)
     */
    protected function syncSekolah(array $r): array
    {
        $kode = $r['sklKode'] ?? null;
        if (!$kode) {
            throw new \Exception('sekolah: sklKode kosong');
        }

        $profile = $r['profile_sekolah'] ?? [];

        $data = [
            'npsn'     => $r['sklNpsn'] ?? null,
            'kode'     => $kode,
            'nama'     => $r['sklNama'] ?? '',
            'alamat'   => $r['sklAlamat'] ?? null,
            'provinsi' => $profile['prsklProvinsi'] ?? null,
            'kota'     => $profile['prsklKabupaten'] ?? null,
        ];

        $existing = LamtimSekolah::where('kode', $kode)->first();

        // Use sklNpsn as the cache key since the API uses it as FK reference
        $cacheKey = (string) ($r['sklNpsn'] ?? $kode);

        if ($existing) {
            $existing->update(array_merge($data, ['external_id' => $cacheKey]));
            $this->cacheId('sekolah', $cacheKey, $existing->id);
            return ['action' => 'updated'];
        }

        $data['external_id'] = $cacheKey;
        $model = LamtimSekolah::create($data);
        $this->cacheId('sekolah', $cacheKey, $model->id);
        return ['action' => 'inserted'];
    }

    /**
     * API: {klsKode, kslSlag, klsNama}
     * Dedup: klsKode → kode
     * Note: No integer ID in API — use klsKode as cache key
     */
    protected function syncKelas(array $r): array
    {
        $kode = $r['klsKode'] ?? null;
        if (!$kode) {
            throw new \Exception('kelas: klsKode kosong');
        }

        $data = [
            'kode' => $kode,
            'nama' => $r['klsNama'] ?? $kode,
        ];

        $existing = LamtimKelas::where('kode', $kode)->first();

        if ($existing) {
            $existing->update(array_merge($data, ['external_id' => $kode]));
            $this->cacheId('kelas', $kode, $existing->id);
            return ['action' => 'updated'];
        }

        $data['external_id'] = $kode;
        $model = LamtimKelas::create($data);
        $this->cacheId('kelas', $kode, $model->id);
        return ['action' => 'inserted'];
    }

    /**
     * API: {id, skl, kode, nama}
     * Dedup: kode → kode
     * skl = API's sekolah reference (probably sklNpsn or integer)
     */
    protected function syncJurusan(array $r): array
    {
        $kode = $r['kode'] ?? null;
        if (!$kode) {
            throw new \Exception('jurusan: kode kosong');
        }

        $data = [
            'kode'        => $kode,
            'nama'        => $r['nama'] ?? $kode,
            'external_id' => (string) ($r['id'] ?? $kode),
        ];

        // Resolve FK: kode_skl → sekolah
        $sklKode = $r['kode_skl'] ?? $r['skl'] ?? null;
        if ($sklKode) {
            $localSekolahId = $this->resolveId('sekolah', (string) $sklKode);
            if ($localSekolahId) {
                $data['idSekolah'] = $localSekolahId;
            }
        }

        $existing = LamtimJurusan::where('kode', $kode)->first();

        if ($existing) {
            $existing->update($data);
            $this->cacheId('jurusan', $kode, $existing->id);
            return ['action' => 'updated'];
        }

        $model = LamtimJurusan::create($data);
        $this->cacheId('jurusan', $kode, $model->id);
        return ['action' => 'inserted'];
    }

    /**
     * API: {rblId, sklKode, jrsKode, klsKode, rblKode, rblNama}
     * Dedup: rblKode → kode
     */
    protected function syncRombel(array $r): array
    {
        $kode = $r['rblKode'] ?? null;
        if (!$kode) {
            throw new \Exception('rombel: rblKode kosong');
        }

        $data = [
            'kode'        => $kode,
            'nama'        => $r['rblNama'] ?? $kode,
            'external_id' => (string) ($r['rblId'] ?? $kode),
        ];

        // Resolve FKs via Kodes
        if ($sklKode = ($r['sklKode'] ?? $r['rblSklId'] ?? null)) {
            $localId = $this->resolveId('sekolah', (string) $sklKode);
            if ($localId) $data['idSekolah'] = $localId;
        }
        if ($jrsKode = ($r['jrsKode'] ?? $r['rblJrsId'] ?? null)) {
            $localId = $this->resolveId('jurusan', (string) $jrsKode);
            if ($localId) $data['idJurusan'] = $localId;
        }
        if ($klsKode = ($r['klsKode'] ?? $r['rblKlsId'] ?? null)) {
            $localId = $this->resolveId('kelas', (string) $klsKode);
            if ($localId) $data['idKelas'] = $localId;
        }

        $existing = LamtimRombel::where('kode', $kode)->first();

        if ($existing) {
            $existing->update($data);
            $this->cacheId('rombel', $kode, $existing->id);
            return ['action' => 'updated'];
        }

        $model = LamtimRombel::create($data);
        $this->cacheId('rombel', $kode, $model->id);
        return ['action' => 'inserted'];
    }

    /**
     * API: {ssaUsername, ssaFullName, ssaSklId, ssaJrsId, ssaRblId, ssaWa, ssaWaOrtu,
     *       ssaTahunAngkata, ssaAgama, ssaFotoOsis, jenis_kelamin,
     *       nis, nisn, tempat_lahir, tanggal_lahir, nama_ibu, nama_ayah, asal_smp, alamat,
     *       profile_siswa{psTpl, psTgl, psNis, psNisn, psAlamat, psNamaIbu, psNamaAyah, master_smp{...}},
     *       master_rombel{rblKode}, master_jurusan{...}, master_sekolah{...}, rfid_user{...}}
     * Dedup: ssaUsername → username
     */
    protected function syncSiswa(array $r): array
    {
        $username = $r['ssaUsername'] ?? null;
        if (!$username) {
            throw new \Exception('siswa: ssaUsername kosong');
        }

        // Map jenis_kelamin: L=0 (Pria), P=1 (Wanita)
        $jsk = null;
        $jkRaw = $r['jenis_kelamin'] ?? ($r['profile_siswa']['psJsk'] ?? null);
        if ($jkRaw !== null) {
            $jsk = strtoupper($jkRaw) === 'P' ? 1 : 0;
        }

        // Sanitize NISN: treat '-', '', ' ' as null to avoid unique constraint violations
        $nisnRaw = $r['nisn'] ?? ($r['profile_siswa']['psNisn'] ?? null);
        $nisn = ($nisnRaw && !in_array(trim($nisnRaw), ['-', '', '0'])) ? trim($nisnRaw) : null;

        // Sanitize NIS similarly
        $nisRaw = $r['nis'] ?? ($r['profile_siswa']['psNis'] ?? $username);
        $nis = ($nisRaw && !in_array(trim((string)$nisRaw), ['-', '', '0'])) ? trim((string)$nisRaw) : $username;

        $data = [
            'username'      => $username,
            'nama'          => $r['ssaFullName'] ?? '',
            'nis'           => $nis,
            'nisn'          => $nisn,
            'waSiswa'       => $r['ssaWa'] ?? null,
            'waOrtu'        => $r['ssaWaOrtu'] ?? null,
            'tahunAngkatan' => $r['ssaTahunAngkata'] ?? null,
            'fotoOsis'      => $r['ssaFotoOsis'] ?? null,
            'isActive'      => 1,
            'external_id'   => $username,
        ];

        if ($jsk !== null) {
            $data['jsk'] = $jsk;
        }

        // Resolve idAgama from lamtim_agama by name (e.g. "ISLAM")
        if ($agama = ($r['ssaAgama'] ?? null)) {
            $localAgamaId = $this->resolveId('agama', strtoupper($agama));
            if ($localAgamaId) {
                $data['idAgama'] = $localAgamaId;
            }
        }

        // Resolve rombel FK for pivot
        $localRombelId = null;
        if ($rblKode = ($r['master_rombel']['rblKode'] ?? null)) {
            $localRombelId = $this->resolveId('rombel', (string) $rblKode);
        }

        $existing = LamtimSiswa::where('username', $username)->first();
        $siswaModel = null;

        if ($existing) {
            $updateData = $data;
            unset($updateData['username']);
            $existing->update($updateData);
            $this->updateSiswaProfile($existing->id, $r);
            $siswaModel = $existing;
            $action = 'updated';
        } else {
            $data['password'] = Hash::make($username);
            $model = new LamtimSiswa();
            $model->forceFill($data);
            $model->save();
            $this->updateSiswaProfile($model->id, $r);
            $siswaModel = $model;
            $action = 'inserted';
        }

        // Update SiswaRombel pivot
        if ($localRombelId && $siswaModel) {
            LamtimSiswaRombel::updateOrCreate(
                ['idSiswa' => $siswaModel->id],
                ['idRombel' => $localRombelId]
            );
        }

        return ['action' => $action];
    }

    // =========================================================================
    // Helper methods
    // =========================================================================

    /**
     * Update or create siswa profile from API data.
     * Maps fields from new JSON format to lamtim_siswa_profiles columns.
     */
    protected function updateSiswaProfile(string $siswaId, array $r): void
    {
        $ps = $r['profile_siswa'] ?? [];
        $masterSmp = $ps['master_smp'] ?? [];

        $profileData = [];

        // Tempat & tanggal lahir (prefer profile_siswa, fallback to top-level)
        $tpl = $ps['psTpl'] ?? ($r['tempat_lahir'] ?? null);
        $tgl = $ps['psTgl'] ?? ($r['tanggal_lahir'] ?? null);
        if ($tpl) $profileData['tempatLahir'] = $tpl;

        // Validate date: skip invalid dates like '-0001-11-30'
        if ($tgl) {
            try {
                $parsed = \Carbon\Carbon::parse($tgl);
                if ($parsed->year >= 1900 && $parsed->year <= 2100) {
                    $profileData['tanggalLahir'] = $parsed->toDateString();
                }
            } catch (\Exception $e) {
                // Skip invalid date silently
            }
        }

        // Alamat
        $alamat = $ps['psAlamat'] ?? ($r['alamat'] ?? null);
        if ($alamat) $profileData['alamat'] = $alamat;

        // Orang tua
        $namaAyah = $ps['psNamaAyah'] ?? ($r['nama_ayah'] ?? null);
        $namaIbu  = $ps['psNamaIbu'] ?? ($r['nama_ibu'] ?? null);
        if ($namaAyah) $profileData['namaAyah'] = $namaAyah;
        if ($namaIbu)  $profileData['namaIbu']  = $namaIbu;

        // Asal SMP
        $asalSekolah = $masterSmp['smpNama'] ?? ($r['asal_smp'] ?? null);
        $alamatSmpAsal = $masterSmp['smpAlamat'] ?? null;
        if ($asalSekolah) $profileData['asalSekolah'] = $asalSekolah;
        if ($alamatSmpAsal) $profileData['alamatSekolahAsal'] = $alamatSmpAsal;

        // Only save if we have data
        if (!empty($profileData)) {
            LamtimSiswaProfile::updateOrCreate(
                ['idSiswa' => $siswaId],
                $profileData
            );
        }
    }

    protected function cacheId(string $entity, string $externalRef, string $localId): void
    {
        $this->idMaps[$entity][$externalRef] = $localId;
    }

    protected function resolveId(string $entity, string $externalRef): ?string
    {
        return $this->idMaps[$entity][$externalRef] ?? null;
    }

    /**
     * Save last sync timestamp to cache.
     */
    public function saveLastSyncTime(): void
    {
        $existing = Cache::get('last_external_sync', []);
        $existingResults = $existing['results'] ?? [];

        $newResults = collect($this->results)->map(fn($r) => [
            'status'       => $r['status'] ?? 'unknown',
            'inserted'     => $r['inserted'] ?? 0,
            'updated'      => $r['updated'] ?? 0,
            'soft_deleted' => $r['soft_deleted'] ?? 0,
            'failed'       => $r['failed'] ?? 0,
            'errors'       => $r['errors'] ?? [],
        ])->toArray();

        $data = [
            'timestamp' => now()->toISOString(),
            'results' => array_merge($existingResults, $newResults),
        ];

        Cache::put('last_external_sync', $data, now()->addYear());
        
        // Clear real-time progress when done
        $this->clearProgress();
    }

    /**
     * Update real-time sync progress in cache.
     */
    public function updateProgress(string $entity, int $processed, int $total, string $stage = 'processing', int $entityIndex = 1, int $totalEntities = 1, array $extraStats = []): void
    {
        $percentage = $total > 0 ? round(($processed / $total) * 100, 2) : 0;
        
        Cache::put('sync_progress', array_merge([
            'entity' => $entity,
            'stage' => $stage, // 'downloading', 'processing', 'completed', 'failed'
            'processed' => $processed,
            'total' => $total,
            'percentage' => $percentage,
            'entity_index' => $entityIndex,
            'total_entities' => $totalEntities,
            'timestamp' => now()->toIso8601String(),
        ], $extraStats), now()->addMinutes(10));
    }

    /**
     * Download API data to local JSON file.
     */
    protected function downloadEntity(string $entity): ?string
    {
        $data = $this->apiClient->fetch($entity);
        if (empty($data)) return null;

        $dir = storage_path('sync');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filePath = "{$dir}/{$entity}.json";
        File::put($filePath, json_encode($data));
        
        return $filePath;
    }

    /**
     * Cleanup temporary sync file.
     */
    protected function cleanupSyncFile(string $entity): void
    {
        $filePath = storage_path("sync/{$entity}.json");
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }

    /**
     * Clear real-time sync progress.
     */
    public function clearProgress(): void
    {
        Cache::forget('sync_progress');
    }

    /**
     * Get real-time sync progress.
     */
    public function getProgress(): ?array
    {
        return Cache::get('sync_progress');
    }

    /**
     * Get the last sync status.
     */
    public function getLastSyncStatus(): ?array
    {
        return Cache::get('last_external_sync');
    }

    /**
     * Get sync statistics.
     */
    public function getSyncStats(): array
    {
        return [
            'tahun_ajaran' => [
                'total' => LamtimTahunAjaran::count(),
                'synced' => LamtimTahunAjaran::whereNotNull('external_id')->count(),
            ],
            'semester' => [
                'total' => LamtimSemester::count(),
                'synced' => LamtimSemester::whereNotNull('external_id')->count(),
            ],
            'sekolah' => [
                'total' => LamtimSekolah::count(),
                'synced' => LamtimSekolah::whereNotNull('external_id')->count(),
            ],
            'kelas' => [
                'total' => LamtimKelas::count(),
                'synced' => LamtimKelas::whereNotNull('external_id')->count(),
            ],
            'jurusan' => [
                'total' => LamtimJurusan::count(),
                'synced' => LamtimJurusan::whereNotNull('external_id')->count(),
            ],
            'rombel' => [
                'total' => LamtimRombel::count(),
                'synced' => LamtimRombel::whereNotNull('external_id')->count(),
            ],
            'siswa' => [
                'total' => LamtimSiswa::count(),
                'synced' => LamtimSiswa::whereNotNull('external_id')->count(),
            ],
        ];
    }
}
