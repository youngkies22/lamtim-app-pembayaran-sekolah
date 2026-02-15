<?php

namespace App\Services;

use App\Models\LamtimTagihan;
use App\Models\LamtimPembayaran;
use App\Services\Interfaces\AcademicIntegrationServiceInterface;
use Illuminate\Support\Facades\Log;

class AcademicIntegrationService implements AcademicIntegrationServiceInterface
{
    protected ExternalApiClient $apiClient;

    public function __construct(ExternalApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @inheritDoc
     */
    public function pushTagihan(LamtimTagihan $tagihan): bool
    {
        try {
            // Ensure relationships are loaded to prevent N+1
            $tagihan->loadMissing([
                'siswa',
                'masterPembayaran',
                'tahunAjaran',
                'semester'
            ]);

            $payload = [
                'identity' => [
                    'username' => $tagihan->siswa?->username ?? '-',
                    'uuid_spp' => $tagihan->id,
                ],
                'billing_detail' => [
                    'kode_tagihan' => $tagihan->kodeTagihan,
                    'nama_pembayaran' => $tagihan->masterPembayaran?->nama ?? '-',
                    'kategori' => $tagihan->masterPembayaran?->kategori ?? '-',
                    'tipe' => $tagihan->masterPembayaran?->jenisPembayaran ?? '-',
                    'tahun_ajaran' => $tagihan->tahunAjaran?->tahun ?? '-',
                    'semester' => $tagihan->semester?->nama ?? '-',
                    'nominal' => (float) $tagihan->nominalTagihan,
                    'terbayar' => (float) $tagihan->totalSudahBayar,
                    'sisa' => (float) $tagihan->totalSisa,
                    'status' => $tagihan->status_label,
                    'tanggal_tagihan' => $tagihan->tanggalTagihan instanceof \Carbon\Carbon ? $tagihan->tanggalTagihan->toDateString() : $tagihan->tanggalTagihan,
                ]
            ];

            $this->apiClient->post('push_tagihan', $payload);

            $tagihan->update([
                'sync_status' => 'success',
                'sync_at' => now(),
                'sync_error' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Academic Integration: Failed to push tagihan', [
                'tagihan_id' => $tagihan->id,
                'error' => $e->getMessage(),
            ]);

            $tagihan->update([
                'sync_status' => 'failed',
                'sync_error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function pushPembayaran(LamtimPembayaran $pembayaran): bool
    {
        try {
            // Ensure relationships are loaded to prevent N+1
            $pembayaran->loadMissing([
                'siswa',
                'tagihan.masterPembayaran',
                'tagihan.tahunAjaran',
                'tagihan.semester'
            ]);

            $payload = [
                'identity' => [
                    'username' => $pembayaran->siswa?->username ?? '-',
                    'uuid_spp' => $pembayaran->tagihan?->id,
                ],
                'payment_detail' => [
                    'uuid_tagihan_spp' => $pembayaran->idTagihan,
                    'uuid_pembayaran_spp' => $pembayaran->id,
                    'username_siswa' => $pembayaran->siswa?->username ?? '-',
                    'nomor_transaksi' => $pembayaran->kodePembayaran,
                    'nominal_bayar' => (float) $pembayaran->nominalBayar,
                    'metode_pembayaran' => $pembayaran->metodeBayar,
                    'tanggal_bayar' => $pembayaran->tanggalBayar instanceof \Carbon\Carbon ? $pembayaran->tanggalBayar->toDateTimeString() : $pembayaran->tanggalBayar,
                    'keterangan' => $pembayaran->keterangan,
                ],
                // Also send updated tagihan state
                'billing_state' => [
                    'total_terbayar' => (float) ($pembayaran->tagihan?->totalSudahBayar ?? 0),
                    'total_sisa' => (float) ($pembayaran->tagihan?->totalSisa ?? 0),
                    'status' => $pembayaran->tagihan?->status_label ?? 'Unknown',
                ]
            ];

            $this->apiClient->post('push_pembayaran', $payload);

            $pembayaran->update([
                'sync_status' => 'success',
                'sync_at' => now(),
                'sync_error' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Academic Integration: Failed to push pembayaran', [
                'pembayaran_id' => $pembayaran->id,
                'error' => $e->getMessage(),
            ]);

            $pembayaran->update([
                'sync_status' => 'failed',
                'sync_error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
