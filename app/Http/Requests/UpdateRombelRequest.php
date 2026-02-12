<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRombelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('rombel');

        return [
            'idSekolah' => 'sometimes|uuid|exists:lamtim_sekolahs,id',
            'idJurusan' => 'sometimes|uuid|exists:lamtim_jurusans,id',
            'idKelas' => 'nullable|uuid|exists:lamtim_kelas,id',
            'kode' => 'sometimes|string|unique:lamtim_rombels,kode,' . $id,
            'nama' => 'sometimes|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'idSekolah.exists' => 'Sekolah tidak ditemukan',
            'idJurusan.exists' => 'Jurusan tidak ditemukan',
            'idKelas.exists' => 'Kelas tidak ditemukan',
            'kode.unique' => 'Kode rombel sudah digunakan',
            'nama.max' => 'Nama rombel maksimal 255 karakter',
        ];
    }
}
