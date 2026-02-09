<?php

namespace App\Services;

use App\Models\LamtimMasterPembayaran;
use App\Models\LamtimSiswa;
use App\Models\LamtimInvoice;

use App\Repositories\Interfaces\PembayaranRepositoryInterface;
use App\Repositories\Interfaces\TagihanRepositoryInterface;
use App\Models\LamtimPembayaran;
use App\Models\LamtimTagihan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembayaranService
{
    protected $pembayaranRepository;
    protected $tagihanRepository;

    public function __construct(
        PembayaranRepositoryInterface $pembayaranRepository,
        TagihanRepositoryInterface $tagihanRepository
    ) {
        $this->pembayaranRepository = $pembayaranRepository;
        $this->tagihanRepository = $tagihanRepository;
    }

    /**
     * Proses pembayaran - Buat invoice + input pembayaran
     * INVOICE DIBUAT SAAT ADA PEMBAYARAN
     */
    public function prosesPembayaran(
        string $idSiswa,
        string $idTagihan,
        float $nominalBayar,
        string $metodeBayar = 'Tunai',
        ?string $buktiBayar = null,
        ?string $keterangan = null
    ): array {
        try {
            DB::beginTransaction();

            // 1. Cari tagihan
            $tagihan = $this->tagihanRepository->find($idTagihan);
            
            if (!$tagihan) {
                throw new \Exception('Tagihan tidak ditemukan');
            }

            // Validasi tagihan milik siswa yang benar
            if ($tagihan->idSiswa !== $idSiswa) {
                throw new \Exception('Tagihan tidak sesuai dengan siswa yang dipilih');
            }

            // Validasi tagihan belum lunas penuh (bisa status 0 atau 3, tapi tidak 1)
            if ($tagihan->status == 1) {
                throw new \Exception('Tagihan sudah lunas');
            }

            // Validasi masih ada sisa yang harus dibayar
            if ($tagihan->totalSisa <= 0) {
                throw new \Exception('Tagihan sudah lunas (tidak ada sisa)');
            }

            // 2. Validasi nominal
            if ($nominalBayar > $tagihan->totalSisa) {
                throw new \Exception('Nominal pembayaran melebihi sisa tagihan');
            }

            // Validasi minimum cicilan
            $master = $tagihan->masterPembayaran;
            if ($master->isCicilan && $master->minCicilan) {
                if ($nominalBayar < $master->minCicilan) {
                    throw new \Exception('Minimum cicilan adalah ' . number_format($master->minCicilan, 0, ',', '.'));
                }
            }

        // Get semester dari tagihan atau current semester
        $idSemester = $tagihan->idSemester;
        if (!$idSemester) {
            $currentMonth = (int)now()->format('m');
            $semesterModel = \App\Models\LamtimSemester::getByBulan($currentMonth);
            $idSemester = $semesterModel?->id;
        }

        // 3. BUAT INVOICE (saat ada pembayaran) dengan snapshot data
        $invoice = LamtimInvoice::create([
            'idSiswa' => $idSiswa,
            'idTagihan' => $tagihan->id,
            'idMasterPembayaran' => $tagihan->idMasterPembayaran,
            'idTahunAjaran' => $tagihan->idTahunAjaran,
            'idRombel' => $tagihan->idRombel,
            'idKelas' => $tagihan->idKelas, // Snapshot from tagihan
            'idJurusan' => $tagihan->idJurusan, // Snapshot from tagihan
            'idSekolah' => $tagihan->idSekolah, // Snapshot from tagihan
            'idSemester' => $idSemester, // Snapshot from tagihan or current
            'noInvoice' => $this->generateNoInvoice($tagihan->idMasterPembayaran, $idSiswa),
            'kodeInvoice' => $this->generateKodeInvoice($tagihan->idMasterPembayaran, $idSiswa),
            'tanggalInvoice' => now(),
            'nominalInvoice' => $nominalBayar,
            'nominalSisa' => $nominalBayar,
            'status' => 0,
            'isActive' => 1,
            'createdBy' => auth()->id(),
        ]);

            // 4. INPUT PEMBAYARAN ke invoice dengan snapshot data
            $pembayaran = $this->pembayaranRepository->create([
                'idSiswa' => $idSiswa,
                'idInvoice' => $invoice->id,
                'idTagihan' => $tagihan->id,
                'idMasterPembayaran' => $tagihan->idMasterPembayaran,
                'idTahunAjaran' => $tagihan->idTahunAjaran, // Snapshot
                'idRombel' => $tagihan->idRombel, // Snapshot
                'idKelas' => $tagihan->idKelas, // Snapshot
                'idJurusan' => $tagihan->idJurusan, // Snapshot
                'idSekolah' => $tagihan->idSekolah, // Snapshot
                'idSemester' => $idSemester, // Snapshot from tagihan or current
                'kodePembayaran' => $this->generateKodePembayaran(),
                'tanggalBayar' => now(),
                'nominalBayar' => $nominalBayar,
                'metodeBayar' => $metodeBayar,
                'channelBayar' => null, // Dihapus, bisa ditulis di keterangan
                'namaChannel' => null, // Dihapus, bisa ditulis di keterangan
                'buktiBayar' => $buktiBayar,
                'keterangan' => $keterangan,
                'status' => 1,
                'isVerified' => 0,
                'isActive' => 1,
                'createdBy' => auth()->id(),
            ]);

            // 5. Update invoice status
            $invoice->updateStatus();

            DB::commit();

            return [
                'invoice' => $invoice->fresh(['tagihan', 'siswa']),
                'pembayaran' => $pembayaran->fresh(['invoice', 'tagihan', 'siswa']),
                'tagihan' => $tagihan->fresh(['siswa', 'masterPembayaran']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing pembayaran', [
                'idSiswa' => $idSiswa,
                'idTagihan' => $idTagihan,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verifikasi pembayaran
     */
    public function verifyPembayaran(string $id): LamtimPembayaran
    {
        try {
            DB::beginTransaction();

            $pembayaran = $this->pembayaranRepository->find($id);
            if (!$pembayaran) {
                throw new \Exception('Pembayaran tidak ditemukan');
            }

            $pembayaran->verify(auth()->id());

            DB::commit();

            return $pembayaran->fresh(['invoice', 'tagihan', 'siswa']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error verifying pembayaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Batalkan pembayaran
     */
    public function cancelPembayaran(string $id, ?string $alasan = null): bool
    {
        try {
            DB::beginTransaction();

            $pembayaran = $this->pembayaranRepository->find($id);
            if (!$pembayaran) {
                throw new \Exception('Pembayaran tidak ditemukan');
            }

            $pembayaran->cancel($alasan, auth()->id());

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling pembayaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate nomor invoice
     */
    private function generateNoInvoice(string $idMasterPembayaran, string $idSiswa): string
    {
        $master = LamtimMasterPembayaran::find($idMasterPembayaran);
        $siswa = LamtimSiswa::find($idSiswa);
        $prefix = strtoupper($master->jenisPembayaran);
        $tahun = now()->format('Y');
        $nis = $siswa->nis ?? substr($siswa->id, 0, 8);
        $count = LamtimInvoice::where('idMasterPembayaran', $idMasterPembayaran)
            ->whereYear('created_at', $tahun)
            ->count() + 1;
        
        return "INV-{$prefix}-{$tahun}-{$nis}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate kode invoice
     */
    private function generateKodeInvoice(string $idMasterPembayaran, string $idSiswa): string
    {
        $master = LamtimMasterPembayaran::find($idMasterPembayaran);
        $siswa = LamtimSiswa::find($idSiswa);
        $prefix = strtoupper($master->jenisPembayaran);
        $tahun = now()->format('Y');
        $nis = $siswa->nis ?? substr($siswa->id, 0, 8);
        $timestamp = now()->format('YmdHis');
        
        return "{$prefix}-{$tahun}-{$nis}-{$timestamp}";
    }

    /**
     * Generate kode pembayaran
     */
    private function generateKodePembayaran(): string
    {
        return 'PAY-' . now()->format('YmdHis');
    }
}
