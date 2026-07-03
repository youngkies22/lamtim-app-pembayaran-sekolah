<?php

namespace App\Http\Requests;

use App\Helpers\CacheHelper;
use App\Models\LamtimTipePembayaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProsesPembayaranRequest extends FormRequest
{
    /**
     * Metode bayar default ketika user tidak memilih apa pun.
     */
    public const DEFAULT_METODE_BAYAR = 'Tunai';

    /**
     * Fallback jika master tipe pembayaran kosong (mis. instalasi baru belum di-seed).
     */
    private const FALLBACK_METODE_BAYAR = ['Tunai', 'Transfer Bank', 'E-Wallet', 'QRIS'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Default-kan metode bayar ke Tunai dan normalisasi ke nama kanonik
     * dari master data (mis. "TUNAI" / "tunai" -> "Tunai").
     */
    protected function prepareForValidation(): void
    {
        $metode = trim((string) $this->input('metodeBayar', ''));

        if ($metode === '') {
            $metode = self::DEFAULT_METODE_BAYAR;
        }

        $canonical = collect($this->allowedMetodeBayar())
            ->first(fn ($nama) => mb_strtolower($nama) === mb_strtolower($metode));

        $this->merge(['metodeBayar' => $canonical ?? $metode]);
    }

    public function rules(): array
    {
        return [
            'idSiswa' => ['required', 'uuid', 'exists:lamtim_siswas,id'],
            'idTagihan' => ['required', 'uuid', 'exists:lamtim_tagihans,id'],
            'nominalBayar' => ['required', 'numeric', 'min:1'],
            'metodeBayar' => ['required', 'string', 'max:50', Rule::in($this->allowedMetodeBayar())],
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
            'metodeBayar.required' => 'Metode pembayaran belum dipilih',
            'metodeBayar.in' => 'Metode pembayaran tidak valid',
        ];
    }

    /**
     * Daftar metode bayar valid diambil dari master tipe pembayaran (cached).
     *
     * @return array<int, string>
     */
    private function allowedMetodeBayar(): array
    {
        $names = CacheHelper::remember(
            ['tipe_pembayaran'],
            'tipe_pembayaran_names',
            3600,
            fn () => LamtimTipePembayaran::where('isActive', 1)
                ->orderBy('nama')
                ->pluck('nama')
                ->all()
        );

        return !empty($names) ? $names : self::FALLBACK_METODE_BAYAR;
    }
}
