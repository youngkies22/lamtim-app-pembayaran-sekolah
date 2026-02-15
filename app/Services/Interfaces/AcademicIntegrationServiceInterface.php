<?php

namespace App\Services\Interfaces;

use App\Models\LamtimTagihan;
use App\Models\LamtimPembayaran;

interface AcademicIntegrationServiceInterface
{
    /**
     * Push tagihan data to academic system.
     *
     * @param LamtimTagihan $tagihan
     * @return bool
     */
    public function pushTagihan(LamtimTagihan $tagihan): bool;

    /**
     * Push pembayaran data to academic system.
     *
     * @param LamtimPembayaran $pembayaran
     * @return bool
     */
    public function pushPembayaran(LamtimPembayaran $pembayaran): bool;
}
