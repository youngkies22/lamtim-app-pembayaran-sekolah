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
        Schema::create('lamtim_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Reference
            $table->uuid('idSiswa');
            $table->uuid('idTagihan')->comment('Reference ke tagihan');
            $table->uuid('idMasterPembayaran')->nullable();
            $table->uuid('idTahunAjaran')->nullable();
            $table->uuid('idRombel')->nullable();
            
            // Data Invoice
            $table->string('noInvoice', 100)->unique()->comment('Nomor invoice unik');
            $table->string('kodeInvoice', 100)->unique();
            $table->date('tanggalInvoice');
            $table->date('tanggalJatuhTempo')->nullable();
            
            // Nominal Invoice
            $table->decimal('nominalInvoice', 15, 2)->comment('Nominal invoice ini (bisa cicilan)');
            $table->decimal('nominalPotongan', 15, 2)->default(0);
            $table->decimal('nominalBayar', 15, 2)->default(0)->comment('Total sudah bayar untuk invoice ini');
            $table->decimal('nominalSisa', 15, 2)->comment('Sisa invoice ini');
            
            // Status Invoice
            $table->tinyInteger('status')->default(0)->comment('0=belum lunas, 1=lunas, 2=terlambat, 3=sebagian');
            $table->date('tanggalLunas')->nullable();
            
            // Keterangan
            $table->text('keterangan')->nullable();
            $table->text('catatan')->nullable();
            
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
            $table->index('idTagihan');
            $table->index('idMasterPembayaran');
            $table->index('noInvoice');
            $table->index('status');
            $table->index('isActive');
            $table->index('tanggalInvoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_invoices');
    }
};
