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
        Schema::create('lamtim_rombels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('idSekolah');
            $table->uuid('idJurusan');
            $table->uuid('idKelas')->nullable()->after('idJurusan')->comment('Kelas default untuk rombel ini');
            $table->string('kode', 100)->unique()->comment('Format: tahun + idJurusan + 3 digit random');
            $table->string('nama', 255);
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=nonaktif');
            $table->timestamps();

            $table->index('idKelas');

            $table->foreign('idSekolah')
                ->references('id')
                ->on('lamtim_sekolahs')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('idJurusan')
                ->references('id')
                ->on('lamtim_jurusans')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Foreign key untuk idKelas akan ditambahkan di migration add_foreign_keys_to_all_tables
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_rombels');
    }
};
