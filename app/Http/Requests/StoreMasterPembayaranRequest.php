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
            'slug' => ['required', 'string', 'max:100'],
            'jenisPembayaran' => ['required', 'string', Rule::exists('lamtim_jenis_pembayarans', 'kode')->where('isActive', 1)],
            'kategori' => ['required', 'string', Rule::exists('lamtim_kategori_pembayarans', 'kode')->where('isActive', 1)],
            'nominal' => ['required', 'numeric', 'min:1'],
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
            'jenisPembayaran.exists' => 'Jenis pembayaran tidak valid atau tidak aktif',
            'kategori.exists' => 'Kategori pembayaran tidak valid atau tidak aktif',
            'nominal.min' => 'Nominal harus lebih besar dari 0',
        ];
    }
}
