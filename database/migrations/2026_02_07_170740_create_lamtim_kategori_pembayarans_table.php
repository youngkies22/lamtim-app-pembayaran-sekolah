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
        Schema::create('lamtim_kategori_pembayarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 50)->unique()->comment('Kode kategori (Rutin, Tambahan, dll)');
            $table->string('nama', 255)->comment('Nama kategori');
            $table->text('deskripsi')->nullable();
            $table->integer('urutan')->default(0)->comment('Urutan tampil');
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=hapus');
            $table->uuid('createdBy')->nullable();
            $table->uuid('updatedBy')->nullable();
            $table->uuid('deletedBy')->nullable();
            $table->timestamps();
            
            $table->index('kode');
            $table->index('isActive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_kategori_pembayarans');
    }
};
