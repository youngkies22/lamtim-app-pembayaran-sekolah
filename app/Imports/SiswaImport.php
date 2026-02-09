<?php

namespace App\Imports;

use App\Models\LamtimSiswa;
use App\Models\LamtimAgama;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsOnFailure
{
    use SkipsFailures;

    protected $importLogId;
    protected $rowNumber = 0;
    protected $errors = [];
    protected $agamaCache = [];

    public function __construct(string $importLogId)
    {
        $this->importLogId = $importLogId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->rowNumber++;

        try {
            // Validate required fields
            if (empty($row['nama']) || empty($row['username'])) {
                $this->errors[] = [
                    'row' => $this->rowNumber + 1, // +1 karena heading row
                    'errors' => ['Nama dan Username wajib diisi']
                ];
                return null;
            }

            // Get agama ID if provided
            $idAgama = null;
            if (!empty($row['agama'])) {
                $idAgama = $this->getAgamaId($row['agama']);
            }

            // Generate password if not provided
            $password = !empty($row['password']) ? $row['password'] : Str::random(8);

            // Map jsk (Jenis Kelamin)
            $jsk = 1; // Default Laki-laki
            if (isset($row['jenis_kelamin'])) {
                $jsk = strtolower($row['jenis_kelamin']) === 'perempuan' || strtolower($row['jenis_kelamin']) === 'p' ? 0 : 1;
            }

            return new LamtimSiswa([
                'idAgama' => $idAgama,
                'username' => $row['username'],
                'nama' => $row['nama'],
                'password' => Hash::make($password),
                'jsk' => $jsk,
                'nis' => $row['nis'] ?? null,
                'nisn' => $row['nisn'] ?? null,
                'waSiswa' => $row['wa_siswa'] ?? null,
                'waOrtu' => $row['wa_ortu'] ?? null,
                'waWali' => $row['wa_wali'] ?? null,
                'tahunAngkatan' => $row['tahun_angkatan'] ?? null,
                'isActive' => 1,
                'createdBy' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            Log::error('SiswaImport error at row ' . $this->rowNumber, [
                'error' => $e->getMessage(),
                'row' => $row
            ]);

            $this->errors[] = [
                'row' => $this->rowNumber + 1,
                'errors' => [$e->getMessage()]
            ];

            return null;
        }
    }

    /**
     * Get agama ID from nama
     */
    protected function getAgamaId(string $nama): ?string
    {
        if (isset($this->agamaCache[$nama])) {
            return $this->agamaCache[$nama];
        }

        $agama = LamtimAgama::where('nama', 'like', "%{$nama}%")->first();
        if ($agama) {
            $this->agamaCache[$nama] = $agama->id;
            return $agama->id;
        }

        return null;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:100',
            'nama' => 'required|string|max:255',
            'nis' => 'nullable|string|max:50',
            'nisn' => 'nullable|string|max:50',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages(): array
    {
        return [
            'username.required' => 'Username wajib diisi',
            'nama.required' => 'Nama wajib diisi',
        ];
    }

    /**
     * Batch size for inserts
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
