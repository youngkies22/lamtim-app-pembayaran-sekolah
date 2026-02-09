<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('siswa') ?? $this->route('id');
        
        return [
            'username' => ['sometimes', 'string', 'max:100', 'unique:lamtim_siswas,username,' . $id],
            'nama' => ['sometimes', 'string', 'max:255'],
            'password' => ['sometimes', 'string', 'min:6'],
            'jsk' => ['sometimes', 'integer', 'in:0,1'],
            'nis' => ['nullable', 'string', 'max:50', 'unique:lamtim_siswas,nis,' . $id],
            'nisn' => ['nullable', 'string', 'max:50', 'unique:lamtim_siswas,nisn,' . $id],
            'idAgama' => ['nullable', 'uuid', 'exists:lamtim_agama,id'],
            'tahunAngkatan' => ['nullable', 'string', 'max:10'],
            'idRombel' => ['nullable', 'uuid', 'exists:lamtim_rombels,id,isActive,1'],
            'isActive' => ['sometimes', 'integer', 'in:0,1,2'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah digunakan.',
            'password.min' => 'Password minimal 6 karakter.',
            'jsk.in' => 'Jenis kelamin harus Perempuan (0) atau Laki-laki (1).',
            'nis.unique' => 'NIS sudah digunakan.',
            'nisn.unique' => 'NISN sudah digunakan.',
            'idAgama.exists' => 'Agama tidak ditemukan.',
            'idRombel.exists' => 'Rombel tidak ditemukan atau tidak aktif.',
            'isActive.in' => 'Status harus 0 (hapus), 1 (aktif), atau 2 (off).',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // If API request, return JSON
        if ($this->expectsJson() || $this->is('api/*')) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }
}
