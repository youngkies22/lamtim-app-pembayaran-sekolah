<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\FormatHelper;
class PembayaranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kodePembayaran' => $this->kodePembayaran,
            'noReferensi' => $this->noReferensi,
            'tanggalBayar' => $this->tanggalBayar?->format('Y-m-d'),
            'tanggalBayarFormatted' => FormatHelper::date($this->tanggalBayar),
            'nominalBayar' => (float) $this->nominalBayar,
            'nominalBayarFormatted' => FormatHelper::currency($this->nominalBayar),
            'metodeBayar' => $this->metodeBayar,
            'channelBayar' => $this->channelBayar,
            'namaChannel' => $this->namaChannel,
            'buktiBayar' => $this->buktiBayar,
            'buktiBayarUrl' => $this->buktiBayar ? asset('storage/' . $this->buktiBayar) : null,
            'keterangan' => $this->keterangan,
            'status' => $this->status,
            'statusLabel' => $this->status == 1 ? 'Valid' : ($this->status == 0 ? 'Batal' : 'Pending'),
            'statusBadge' => FormatHelper::statusBadge($this->status, 'pembayaran'),
            'isVerified' => (bool) $this->isVerified,
            'verifiedBadge' => FormatHelper::statusBadge($this->isVerified ? 1 : 0, 'verifikasi'),
            'verifiedBy' => $this->verifiedBy,
            'verifiedAt' => $this->verifiedAt?->format('Y-m-d H:i:s'),
            'alasanBatal' => $this->alasanBatal,
            'isActive' => (bool) $this->isActive,
            'siswa' => $this->whenLoaded('siswa', function() {
                return [
                    'id' => $this->siswa->id,
                    'nama' => $this->siswa->nama,
                    'nis' => $this->siswa->nis,
                    'rombel_nama' => $this->siswa->getFormattedRombel() ?? '-',
                ];
            }),
            'invoice' => $this->whenLoaded('invoice', function() {
                return [
                    'id' => $this->invoice->id,
                    'noInvoice' => $this->invoice->noInvoice,
                    'nominalInvoice' => (float) $this->invoice->nominalInvoice,
                ];
            }),
            'tagihan' => $this->whenLoaded('tagihan', function() {
                return [
                    'id' => $this->tagihan->id,
                    'kodeTagihan' => $this->tagihan->kodeTagihan,
                ];
            }),
            'masterPembayaran' => $this->whenLoaded('masterPembayaran', function() {
                return [
                    'id' => $this->masterPembayaran->id,
                    'kode' => $this->masterPembayaran->kode,
                    'nama' => $this->masterPembayaran->nama,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
