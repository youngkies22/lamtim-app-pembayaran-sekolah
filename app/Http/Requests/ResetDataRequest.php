<?php

namespace App\Http\Requests;

use App\Services\ResetDataService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResetDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'string', Rule::in(array_keys(ResetDataService::CATEGORY_MAP))],
        ];
    }

    public function messages(): array
    {
        return [
            'categories.required' => 'Pilih minimal satu kategori data untuk direset',
            'categories.min' => 'Pilih minimal satu kategori data untuk direset',
            'categories.*.in' => 'Kategori data tidak dikenali',
        ];
    }
}
