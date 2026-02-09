<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lamtim_pembayarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Reference
            $table->uuid('idSiswa');
            $table->uuid('idInvoice')->comment('Reference ke invoice');
            $table->uuid('idTagihan')->nullable()->comment('Reference ke tagihan (untuk tracking)');
            $table->uuid('idMasterPembayaran')->nullable();
            
            // Data Pembayaran
            $table->string('kodePembayaran', 100)->unique();
            $table->string('noReferensi', 100)->nullable()->comment('No referensi pembayaran');
            $table->date('tanggalBayar');
            $table->decimal('nominalBayar', 15, 2)->comment('Nominal pembayaran ini');
            
            // Metode Pembayaran
            $table->string('metodeBayar', 50)->nullable()->comment('Tunai, Transfer, QRIS, dll');
            $table->string('channelBayar', 50)->nullable()->comment('Bank, E-Wallet, dll');
            $table->string('namaChannel', 100)->nullable()->comment('BCA, BNI, OVO, dll');
            
            // Bukti Pembayaran
            $table->string('buktiBayar', 255)->nullable()->comment('Path file bukti');
            $table->text('keterangan')->nullable();
            
            // Status
            $table->tinyInteger('status')->default(1)->comment('1=valid, 0=batal, 2=pending');
            $table->tinyInteger('isVerified')->default(0)->comment('0=belum verifikasi, 1=sudah verifikasi');
            $table->uuid('verifiedBy')->nullable();
            $table->timestamp('verifiedAt')->nullable();
            $table->text('alasanBatal')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            // Soft Delete
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=hapus');
            
            // Audit
            $table->uuid('createdBy')->nullable();
            $table->uuid('updatedBy')->nullable();
            $table->uuid('deletedBy')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('idSiswa');
            $table->index('idInvoice');
            $table->index('idTagihan');
            $table->index('idMasterPembayaran');
            $table->index('kodePembayaran');
            $table->index('tanggalBayar');
            $table->index('status');
            $table->index('isVerified');
            $table->index('isActive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_pembayarans');
    }
};
