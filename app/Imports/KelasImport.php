<?php

namespace App\Imports;

use App\Models\LamtimKelas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\Log;

class KelasImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsOnFailure
{
    use SkipsFailures;

    protected $importLogId;
    protected $rowNumber = 0;
    protected $errors = [];

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
            if (empty($row['kode']) || empty($row['nama'])) {
                $this->errors[] = [
                    'row' => $this->rowNumber + 1,
                    'errors' => ['Kode dan Nama wajib diisi']
                ];
                return null;
            }

            return new LamtimKelas([
                'kode' => $row['kode'],
                'nama' => $row['nama'],
            ]);
        } catch (\Exception $e) {
            Log::error('KelasImport error at row ' . $this->rowNumber, [
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
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
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
