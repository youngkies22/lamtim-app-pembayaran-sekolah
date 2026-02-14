<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated Migration: All Siswa Tables.
 *
 * Tables created:
 *  - lamtim_siswas
 *  - lamtim_siswa_profiles
 *  - lamtim_siswa_rombels
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================
        // SISWA
        // ============================================
        Schema::create('lamtim_siswas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->nullable();
            $table->uuid('idAgama')->nullable();
            $table->string('username', 100)->unique();
            $table->string('nama', 255);
            $table->string('password', 255);
            $table->tinyInteger('jsk')->default(0)->comment('0=Pria, 1=Wanita');
            $table->string('nis', 50)->nullable()->unique();
            $table->string('nisn', 50)->nullable()->unique();
            $table->string('qrcode', 255)->nullable();
            $table->string('fotoOsis', 255)->nullable();
            $table->string('fotoProfile', 255)->nullable();
            $table->string('waSiswa', 20)->nullable();
            $table->string('waOrtu', 20)->nullable();
            $table->string('waWali', 20)->nullable();
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=hapus, 2=off');
            $table->string('tahunAngkatan', 10)->nullable();
            $table->uuid('createdBy')->nullable();
            $table->uuid('updatedBy')->nullable();
            $table->uuid('deletedBy')->nullable();
            $table->timestamps();

            $table->index('external_id');
            $table->index('username');
            $table->index('nis');
            $table->index('nisn');
            $table->index('isActive');
            $table->index('tahunAngkatan');
            $table->index('idAgama');
        });

        // ============================================
        // SISWA PROFILES
        // ============================================
        Schema::create('lamtim_siswa_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('idSiswa');

            // Data Diri
            $table->string('tempatLahir', 100)->nullable();
            $table->date('tanggalLahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kodePos', 10)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('email', 100)->nullable();

            // Data Orang Tua
            $table->string('namaAyah', 255)->nullable();
            $table->string('pekerjaanAyah', 100)->nullable();
            $table->string('teleponAyah', 20)->nullable();
            $table->string('namaIbu', 255)->nullable();
            $table->string('pekerjaanIbu', 100)->nullable();
            $table->string('teleponIbu', 20)->nullable();

            // Data Wali
            $table->string('namaWali', 255)->nullable();
            $table->string('hubunganWali', 50)->nullable();
            $table->string('pekerjaanWali', 100)->nullable();
            $table->string('teleponWali', 20)->nullable();
            $table->text('alamatWali')->nullable();

            // Pendidikan
            $table->string('asalSekolah', 255)->nullable();
            $table->string('alamatSekolahAsal', 255)->nullable();
            $table->string('noIjazah', 100)->nullable();
            $table->string('noSKHUN', 100)->nullable();

            $table->timestamps();

            $table->index('idSiswa');
        });

        // ============================================
        // SISWA ROMBELS
        // ============================================
        Schema::create('lamtim_siswa_rombels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('idSiswa');
            $table->uuid('idRombel')->nullable();
            $table->uuid('idKelas')->nullable();
            $table->uuid('idTahunAjaran')->nullable();
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=hapus');
            $table->timestamps();

            $table->index('idSiswa');
            $table->index('idRombel');
            $table->index('idKelas');
            $table->index('idTahunAjaran');
            $table->index('isActive');
            $table->unique(['idSiswa', 'idRombel', 'idTahunAjaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_siswa_rombels');
        Schema::dropIfExists('lamtim_siswa_profiles');
        Schema::dropIfExists('lamtim_siswas');
    }
};
