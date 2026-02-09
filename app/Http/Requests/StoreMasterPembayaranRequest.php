<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMasterPembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your auth logic
    }

    public function rules(): array
    {
        return [
            'kode' => ['nullable', 'string', 'max:100', 'unique:lamtim_master_pembayarans,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'jenisPembayaran' => ['required', 'string', 'in:SPP,PKL,KI,UKOM,LAINNYA'],
            'kategori' => ['required', 'string', 'in:BULANAN,TAMBAHAN'],
            'nominal' => ['required', 'numeric', 'min:0'],
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

    public function messages(): array
    {
        return [
            'kode.unique' => 'Kode master pembayaran sudah digunakan',
            'jenisPembayaran.in' => 'Jenis pembayaran harus salah satu dari: SPP, PKL, KI, UKOM, LAINNYA',
            'kategori.in' => 'Kategori harus salah satu dari: BULANAN, TAMBAHAN',
            'nominal.min' => 'Nominal harus lebih besar dari 0',
        ];
    }
}
