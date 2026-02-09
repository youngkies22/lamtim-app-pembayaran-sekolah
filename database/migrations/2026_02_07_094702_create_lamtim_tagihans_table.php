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
        Schema::create('lamtim_tagihans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Reference
            $table->uuid('idSiswa');
            $table->uuid('idMasterPembayaran')->comment('Reference ke master pembayaran');
            $table->uuid('idTahunAjaran')->nullable();
            $table->uuid('idRombel')->nullable();
            
            // Data Tagihan
            $table->string('kodeTagihan', 100)->unique();
            $table->date('tanggalTagihan');
            $table->date('tanggalJatuhTempo')->nullable();
            
            // Nominal
            $table->decimal('nominalTagihan', 15, 2)->comment('Total nominal tagihan dari master');
            $table->decimal('nominalPotongan', 15, 2)->default(0)->comment('Total potongan');
            $table->decimal('totalSudahBayar', 15, 2)->default(0)->comment('SUM dari semua invoice yang sudah lunas');
            $table->decimal('totalSisa', 15, 2)->comment('Kekurangan yang harus dibayar');
            
            // Untuk SPP (bulanan)
            $table->string('bulan', 10)->nullable()->comment('Format: YYYY-MM');
            $table->string('namaBulan', 50)->nullable()->comment('Januari 2026');
            $table->integer('bulanKe')->nullable()->comment('Bulan ke berapa (1-12)');
            
            // Status
            $table->tinyInteger('status')->default(0)->comment('0=belum lunas, 1=lunas, 2=terlambat, 3=sebagian');
            $table->date('tanggalLunas')->nullable();
            $table->integer('hariTerlambat')->default(0)->comment('Hari keterlambatan');
            
            // Keterangan
            $table->text('keterangan')->nullable();
            $table->text('catatan')->nullable();
            
            // Soft Delete
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=hapus');
            
            // Audit
            $table->uuid('createdBy')->nullable();
            $table->uuid('updatedBy')->nullable();
            $table->uuid('deletedBy')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('idSiswa');
            $table->index('idMasterPembayaran');
            $table->index(['idSiswa', 'idMasterPembayaran']); // Composite
            $table->index('status');
            $table->index('isActive');
            $table->index('bulan');
            $table->index('tanggalJatuhTempo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_tagihans');
    }
};
