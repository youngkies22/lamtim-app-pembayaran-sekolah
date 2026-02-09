<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterPembayaranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'nama' => $this->nama,
            'jenisPembayaran' => $this->jenisPembayaran,
            'kategori' => $this->kategori,
            'nominal' => (float) $this->nominal,
            'nominalFormatted' => \App\Helpers\FormatHelper::currency($this->nominal),
            'isCicilan' => (bool) $this->isCicilan,
            'minCicilan' => $this->minCicilan ? (float) $this->minCicilan : null,
            'minCicilanFormatted' => $this->minCicilan ? \App\Helpers\FormatHelper::currency($this->minCicilan) : null,
            'jumlahBulan' => $this->jumlahBulan,
            'nominalPerBulan' => $this->nominalPerBulan ? (float) $this->nominalPerBulan : null,
            'nominalPerBulanFormatted' => $this->nominalPerBulan ? \App\Helpers\FormatHelper::currency($this->nominalPerBulan) : null,
            'periode' => $this->periode,
            'tanggalMulai' => $this->tanggalMulai?->format('Y-m-d'),
            'tanggalSelesai' => $this->tanggalSelesai?->format('Y-m-d'),
            'isActive' => (bool) $this->isActive,
            'isWajib' => (bool) $this->isWajib,
            'keterangan' => $this->keterangan,
            'idTahunAjaran' => $this->idTahunAjaran,
            'idSekolah' => $this->idSekolah,
            'idJurusan' => $this->idJurusan,
            'idRombel' => $this->idRombel,
            'idKelas' => $this->idKelas,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
