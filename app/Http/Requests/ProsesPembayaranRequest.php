<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProsesPembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idSiswa' => ['required', 'uuid', 'exists:lamtim_siswas,id'],
            'idTagihan' => ['required', 'uuid', 'exists:lamtim_tagihans,id'],
            'nominalBayar' => ['required', 'numeric', 'min:1'],
            'metodeBayar' => ['required', 'string', 'in:Tunai,Transfer,QRIS,Debit,Kredit,LAINNYA'],
            'buktiBayar' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'idSiswa.exists' => 'Siswa tidak ditemukan',
            'idTagihan.exists' => 'Tagihan tidak ditemukan',
            'nominalBayar.min' => 'Nominal pembayaran harus lebih besar dari 0',
            'metodeBayar.in' => 'Metode pembayaran tidak valid',
        ];
    }
}
