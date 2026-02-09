<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagihanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kodeTagihan' => $this->kodeTagihan,
            'tanggalTagihan' => $this->tanggalTagihan?->format('Y-m-d'),
            'nominalTagihan' => (float) $this->nominalTagihan,
            'nominalTagihanFormatted' => \App\Helpers\FormatHelper::currency($this->nominalTagihan),
            'nominalPotongan' => (float) $this->nominalPotongan,
            'nominalPotonganFormatted' => \App\Helpers\FormatHelper::currency($this->nominalPotongan),
            'totalSudahBayar' => (float) $this->totalSudahBayar,
            'totalSudahBayarFormatted' => \App\Helpers\FormatHelper::currency($this->totalSudahBayar),
            'totalSisa' => (float) $this->totalSisa,
            'totalSisaFormatted' => \App\Helpers\FormatHelper::currency($this->totalSisa),
            'bulan' => $this->bulan,
            'namaBulan' => $this->namaBulan,
            'bulanKe' => $this->bulanKe,
            'status' => $this->status,
            'statusLabel' => $this->statusLabel,
            'statusBadge' => \App\Helpers\FormatHelper::statusBadge($this->status, 'tagihan'),
            'tanggalLunas' => $this->tanggalLunas?->format('Y-m-d'),
            'hariTerlambat' => $this->hariTerlambat,
            'persentase' => $this->nominalTagihan > 0 
                ? \App\Helpers\FormatHelper::percentage($this->totalSudahBayar, $this->nominalTagihan) 
                : '0%',
            'keterangan' => $this->keterangan,
            'catatan' => $this->catatan,
            'isActive' => (bool) $this->isActive,
            'siswa' => $this->whenLoaded('siswa', function() {
                return [
                    'id' => $this->siswa->id,
                    'nama' => $this->siswa->nama,
                    'nis' => $this->siswa->nis,
                    'nisn' => $this->siswa->nisn,
                ];
            }),
            'masterPembayaran' => $this->whenLoaded('masterPembayaran', function() {
                return [
                    'id' => $this->masterPembayaran->id,
                    'kode' => $this->masterPembayaran->kode,
                    'nama' => $this->masterPembayaran->nama,
                    'jenisPembayaran' => $this->masterPembayaran->jenisPembayaran,
                    'kategori' => $this->masterPembayaran->kategori,
                ];
            }),
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
