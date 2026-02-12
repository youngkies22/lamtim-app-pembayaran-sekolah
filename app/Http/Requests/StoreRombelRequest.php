<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRombelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idSekolah' => 'required|uuid|exists:lamtim_sekolahs,id',
            'idJurusan' => 'required|uuid|exists:lamtim_jurusans,id',
            'idKelas' => 'nullable|uuid|exists:lamtim_kelas,id',
            'kode' => 'required|string|unique:lamtim_rombels,kode',
            'nama' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'idSekolah.required' => 'Sekolah wajib dipilih',
            'idSekolah.exists' => 'Sekolah tidak ditemukan',
            'idJurusan.required' => 'Jurusan wajib dipilih',
            'idJurusan.exists' => 'Jurusan tidak ditemukan',
            'idKelas.exists' => 'Kelas tidak ditemukan',
            'kode.required' => 'Kode rombel wajib diisi',
            'kode.unique' => 'Kode rombel sudah digunakan',
            'nama.required' => 'Nama rombel wajib diisi',
            'nama.max' => 'Nama rombel maksimal 255 karakter',
        ];
    }
}
