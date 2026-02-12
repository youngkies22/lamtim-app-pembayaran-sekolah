<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_aplikasi' => 'nullable|string|max:255',
            'logo_aplikasi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_aplikasi.max' => 'Nama aplikasi maksimal 255 karakter',
            'logo_aplikasi.image' => 'File harus berupa gambar',
            'logo_aplikasi.mimes' => 'Format gambar harus: jpeg, png, jpg, gif, svg, webp',
            'logo_aplikasi.max' => 'Ukuran gambar maksimal 2MB',
        ];
    }
}
