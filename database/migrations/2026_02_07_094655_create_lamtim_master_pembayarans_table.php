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
        Schema::create('lamtim_master_pembayarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Reference
            $table->uuid('idTahunAjaran')->nullable();
            $table->uuid('idSekolah')->nullable();
            $table->uuid('idJurusan')->nullable();
            $table->uuid('idRombel')->nullable();
            $table->uuid('idKelas')->nullable();
            
            // Data Master
            $table->string('kode', 100)->unique();
            $table->string('nama', 255);
            $table->string('jenisPembayaran', 50)->comment('SPP, PKL, KI, UKOM, LAINNYA');
            $table->string('kategori', 50)->comment('Rutin, Tambahan');
            
            // Nominal
            $table->decimal('nominal', 15, 2)->comment('Total nominal yang harus dibayar');
            $table->tinyInteger('isCicilan')->default(1)->comment('1=bisa cicil, 0=harus lunas');
            $table->integer('minCicilan')->nullable()->comment('Minimum cicilan per invoice');
            
            // Untuk SPP (bulanan)
            $table->integer('jumlahBulan')->nullable()->comment('Jumlah bulan (untuk SPP)');
            $table->decimal('nominalPerBulan', 15, 2)->nullable()->comment('Nominal per bulan');
            
            // Periode
            $table->string('periode', 50)->nullable();
            $table->date('tanggalMulai')->nullable();
            $table->date('tanggalSelesai')->nullable();
            
            // Status
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=hapus');
            $table->tinyInteger('isWajib')->default(1)->comment('1=wajib, 0=opsional');
            $table->text('keterangan')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable()->comment('Data tambahan dalam format JSON');
            
            // Audit
            $table->uuid('createdBy')->nullable();
            $table->uuid('updatedBy')->nullable();
            $table->uuid('deletedBy')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('jenisPembayaran');
            $table->index('kategori');
            $table->index('isActive');
            $table->index('idTahunAjaran');
            $table->index('idKelas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_master_pembayarans');
    }
};
