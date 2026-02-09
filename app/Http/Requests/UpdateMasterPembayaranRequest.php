<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMasterPembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('master_pembayaran') ?? $this->route('id');
        
        return [
            'kode' => ['sometimes', 'string', 'max:100', Rule::unique('lamtim_master_pembayarans', 'kode')->ignore($id)],
            'nama' => ['sometimes', 'string', 'max:255'],
            'jenisPembayaran' => ['sometimes', 'string', 'in:SPP,PKL,KI,UKOM,LAINNYA'],
            'kategori' => ['sometimes', 'string', 'in:BULANAN,TAMBAHAN'],
            'nominal' => ['sometimes', 'numeric', 'min:0'],
            'isCicilan' => ['sometimes', 'boolean'],
            'minCicilan' => ['nullable', 'numeric', 'min:0'],
            'jumlahBulan' => ['nullable', 'integer', 'min:1'],
            'nominalPerBulan' => ['nullable', 'numeric', 'min:0'],
            'periode' => ['nullable', 'string', 'max:50'],
            'tanggalMulai' => ['nullable', 'date'],
            'tanggalSelesai' => ['nullable', 'date', 'after_or_equal:tanggalMulai'],
            'isWajib' => ['sometimes', 'boolean'],
            'keterangan' => ['nullable', 'string'],
            'idTahunAjaran' => ['nullable', 'uuid', 'exists:lamtim_tahun_ajarans,id'],
            'idSekolah' => ['nullable', 'uuid', 'exists:lamtim_sekolahs,id'],
            'idJurusan' => ['nullable', 'uuid', 'exists:lamtim_jurusans,id'],
            'idRombel' => ['nullable', 'uuid', 'exists:lamtim_rombels,id'],
            'idKelas' => ['nullable', 'uuid', 'exists:lamtim_kelas,id'],
        ];
    }
}
