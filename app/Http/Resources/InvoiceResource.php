<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Get tahun ajaran aktif
        $tahunAjaranAktif = \App\Models\LamtimTahunAjaran::active()->first();
        
        // Get sekolah (ambil sekolah pertama dari database)
        $sekolah = \App\Models\LamtimSekolah::first();
        
        // Get rombel siswa aktif (ambil yang terbaru)
        $rombelSiswa = null;
        if ($this->siswa) {
            $siswaRombel = $this->siswa->rombels()
                ->with(['rombel.kelas', 'rombel.jurusan'])
                ->orderBy('created_at', 'desc')
                ->first();
            if ($siswaRombel && $siswaRombel->rombel) {
                $rombelSiswa = $siswaRombel->rombel;
            }
        }
        
        // Get semua tagihan siswa yang aktif
        $semuaTagihan = [];
        if ($this->siswa) {
            $tagihanList = \App\Models\LamtimTagihan::where('idSiswa', $this->siswa->id)
                ->where('isActive', 1)
                ->with(['masterPembayaran:id,kode,nama'])
                ->get();
            foreach ($tagihanList as $t) {
                $semuaTagihan[] = [
                    'id' => $t->id,
                    'kodeTagihan' => $t->kodeTagihan,
                    'nama' => $t->masterPembayaran->nama ?? '-',
                    'nominalTagihan' => (float) $t->nominalTagihan,
                    'totalSudahBayar' => (float) $t->totalSudahBayar,
                    'totalSisa' => (float) $t->totalSisa,
                    'status' => $t->status,
                ];
            }
        }
        
        return [
            'id' => $this->id,
            'noInvoice' => $this->noInvoice,
            'kodeInvoice' => $this->kodeInvoice,
            'tanggalInvoice' => $this->tanggalInvoice?->format('Y-m-d'),
            'tanggalInvoiceFormatted' => \App\Helpers\FormatHelper::date($this->tanggalInvoice),
            'nominalInvoice' => (float) $this->nominalInvoice,
            'nominalInvoiceFormatted' => \App\Helpers\FormatHelper::currency($this->nominalInvoice),
            'nominalPotongan' => (float) $this->nominalPotongan,
            'nominalPotonganFormatted' => \App\Helpers\FormatHelper::currency($this->nominalPotongan),
            'nominalBayar' => (float) $this->nominalBayar,
            'nominalBayarFormatted' => \App\Helpers\FormatHelper::currency($this->nominalBayar),
            'nominalSisa' => (float) $this->nominalSisa,
            'nominalSisaFormatted' => \App\Helpers\FormatHelper::currency($this->nominalSisa),
            'status' => $this->status,
            'statusLabel' => $this->statusLabel,
            'statusBadge' => \App\Helpers\FormatHelper::statusBadge($this->status, 'tagihan'),
            'tanggalLunas' => $this->tanggalLunas?->format('Y-m-d'),
            'keterangan' => $this->keterangan,
            'catatan' => $this->catatan,
            'isActive' => (bool) $this->isActive,
            
            // Tahun Ajaran Aktif
            'tahunAjaran' => $tahunAjaranAktif ? [
                'id' => $tahunAjaranAktif->id,
                'nama' => $tahunAjaranAktif->nama,
                'tahun' => $tahunAjaranAktif->tahun,
            ] : null,
            
            // Sekolah
            'sekolah' => $sekolah ? [
                'id' => $sekolah->id,
                'nama' => $sekolah->nama,
                'namaYayasan' => $sekolah->namaYayasan,
            ] : null,
            
            // Siswa dengan Sekolah dan Rombel
            'siswa' => $this->whenLoaded('siswa', function() use ($rombelSiswa, $sekolah) {
                return [
                    'id' => $this->siswa->id,
                    'nama' => $this->siswa->nama,
                    'nis' => $this->siswa->nis,
                    'kelas' => $rombelSiswa ? ($rombelSiswa->kelas->nama ?? '') . ' ' . ($rombelSiswa->nama ?? '') : '-',
                    'rombel' => $rombelSiswa ? [
                        'id' => $rombelSiswa->id,
                        'nama' => $rombelSiswa->nama,
                        'kelas' => $rombelSiswa->kelas->nama ?? '-',
                        'jurusan' => $rombelSiswa->jurusan->nama ?? '-',
                    ] : null,
                    'sekolah' => $sekolah ? [
                        'id' => $sekolah->id,
                        'nama' => $sekolah->nama,
                        'namaYayasan' => $sekolah->namaYayasan ?? $sekolah->nama,
                    ] : null,
                ];
            }),
            
            // Tagihan saat ini
            'tagihan' => $this->whenLoaded('tagihan', function() {
                return [
                    'id' => $this->tagihan->id,
                    'kodeTagihan' => $this->tagihan->kodeTagihan,
                    'nominalTagihan' => (float) $this->tagihan->nominalTagihan,
                    'totalSudahBayar' => (float) $this->tagihan->totalSudahBayar,
                    'totalSisa' => (float) $this->tagihan->totalSisa,
                    'masterPembayaran' => $this->tagihan->masterPembayaran ? [
                        'id' => $this->tagihan->masterPembayaran->id,
                        'kode' => $this->tagihan->masterPembayaran->kode,
                        'nama' => $this->tagihan->masterPembayaran->nama,
                    ] : null,
                ];
            }),
            
            // Semua tagihan siswa
            'semuaTagihan' => $semuaTagihan,
            
            'masterPembayaran' => $this->whenLoaded('masterPembayaran', function() {
                return [
                    'id' => $this->masterPembayaran->id,
                    'kode' => $this->masterPembayaran->kode,
                    'nama' => $this->masterPembayaran->nama,
                ];
            }),
            'pembayarans' => PembayaranResource::collection($this->whenLoaded('pembayarans')),
            
            // Admin yang memproses
            'admin' => $this->createdBy ? [
                'id' => $this->createdBy,
                'nama' => \App\Models\User::find($this->createdBy)?->name ?? 'Admin',
            ] : null,
            
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
