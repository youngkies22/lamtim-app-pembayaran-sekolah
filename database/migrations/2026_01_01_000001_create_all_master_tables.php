<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated Migration: All Master & Reference Tables.
 *
 * Tables created:
 *  - lamtim_tahun_ajarans
 *  - lamtim_sekolahs
 *  - lamtim_jurusans
 *  - lamtim_kelas
 *  - lamtim_agama
 *  - lamtim_semesters
 *  - lamtim_rombels
 *  - lamtim_jenis_pembayarans
 *  - lamtim_kategori_pembayarans
 *  - lamtim_tipe_pembayarans
 *  - lamtim_settings
 *  - lamtim_import_logs
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================
        // TAHUN AJARAN
        // ============================================
        Schema::create('lamtim_tahun_ajarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->nullable();
            $table->string('kode', 50)->unique();
            $table->string('tahun', 20)->comment('2025/2026');
            $table->date('tanggalMulai')->nullable();
            $table->date('tanggalSelesai')->nullable();
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=hapus');
            $table->timestamps();

            $table->index('external_id');
            $table->index('tahun');
            $table->index('isActive');
        });

        // ============================================
        // SEKOLAH
        // ============================================
        Schema::create('lamtim_sekolahs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->nullable();
            $table->string('npsn', 20)->nullable();
            $table->string('kode', 50)->nullable()->unique();
            $table->string('nama', 255);
            $table->string('namaYayasan', 255)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('akreditasi', 50)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('logo')->nullable();
            $table->string('kepala_sekolah', 255)->nullable();
            $table->string('nip_kepsek', 50)->nullable();
            $table->timestamps();

            $table->index('external_id');
            $table->index('npsn');
            $table->index('kode');
        });

        // ============================================
        // JURUSAN
        // ============================================
        Schema::create('lamtim_jurusans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->nullable();
            $table->uuid('idSekolah')->nullable();
            $table->string('kode', 50)->unique();
            $table->string('nama', 255);
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=hapus');
            $table->timestamps();

            $table->index('external_id');
            $table->index('idSekolah');
            $table->index('isActive');
        });

        // ============================================
        // KELAS
        // ============================================
        Schema::create('lamtim_kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->nullable();
            $table->string('kode', 50)->unique();
            $table->string('nama', 100);
            $table->tinyInteger('isActive')->default(1);
            $table->timestamps();

            $table->index('external_id');
            $table->index('isActive');
        });

        // ============================================
        // AGAMA
        // ============================================
        Schema::create('lamtim_agama', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 50)->unique();
            $table->string('nama', 100);
            $table->tinyInteger('isActive')->default(1);
            $table->timestamps();

            $table->index('isActive');
        });

        // ============================================
        // SEMESTER
        // ============================================
        Schema::create('lamtim_semesters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->nullable();
            $table->string('kode', 100)->unique()->comment('Ganjil, Genap');
            $table->string('nama', 255)->comment('Semester Ganjil, Semester Genap');
            $table->string('namaSingkat', 50)->nullable()->comment('Ganjil, Genap');
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=nonaktif');
            $table->timestamps();

            $table->index('external_id');
            $table->index('kode');
            $table->index('isActive');
        });

        // ============================================
        // ROMBEL
        // ============================================
        Schema::create('lamtim_rombels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id', 100)->nullable();
            $table->uuid('idSekolah')->nullable();
            $table->uuid('idJurusan')->nullable();
            $table->uuid('idTahunAjaran')->nullable();
            $table->uuid('idKelas')->nullable();
            $table->string('kode', 50)->unique();
            $table->string('nama', 255);
            $table->string('tingkat', 20)->nullable()->comment('X, XI, XII');
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=hapus');
            $table->timestamps();

            $table->index('external_id');
            $table->index('idSekolah');
            $table->index('idJurusan');
            $table->index('idTahunAjaran');
            $table->index('idKelas');
            $table->index('tingkat');
            $table->index('isActive');
        });

        // ============================================
        // JENIS PEMBAYARAN
        // ============================================
        Schema::create('lamtim_jenis_pembayarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 50)->unique();
            $table->string('nama', 255);
            $table->text('keterangan')->nullable();
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=nonaktif');
            $table->timestamps();

            $table->index('kode');
            $table->index('isActive');
        });

        // ============================================
        // KATEGORI PEMBAYARAN
        // ============================================
        Schema::create('lamtim_kategori_pembayarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 50)->unique();
            $table->string('nama', 255);
            $table->text('keterangan')->nullable();
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=nonaktif');
            $table->timestamps();

            $table->index('kode');
            $table->index('isActive');
        });

        // ============================================
        // TIPE PEMBAYARAN
        // ============================================
        Schema::create('lamtim_tipe_pembayarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 50)->unique();
            $table->string('nama', 255);
            $table->text('keterangan')->nullable();
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=nonaktif');
            $table->timestamps();

            $table->index('kode');
            $table->index('isActive');
        });

        // ============================================
        // SETTINGS (kolom langsung, bukan key-value)
        // ============================================
        Schema::create('lamtim_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_aplikasi', 255)->nullable();
            $table->string('logo_aplikasi', 500)->nullable();
            $table->timestamps();
        });

        // ============================================
        // IMPORT LOGS
        // ============================================
        Schema::create('lamtim_import_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 50)->comment('siswa, tagihan, pembayaran, etc');
            $table->string('filename', 255);
            $table->integer('totalRows')->default(0);
            $table->integer('successRows')->default(0);
            $table->integer('failedRows')->default(0);
            $table->json('errors')->nullable();
            $table->string('status', 20)->default('processing')->comment('processing, completed, failed');
            $table->uuid('createdBy')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('createdBy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_import_logs');
        Schema::dropIfExists('lamtim_settings');
        Schema::dropIfExists('lamtim_tipe_pembayarans');
        Schema::dropIfExists('lamtim_kategori_pembayarans');
        Schema::dropIfExists('lamtim_jenis_pembayarans');
        Schema::dropIfExists('lamtim_rombels');
        Schema::dropIfExists('lamtim_semesters');
        Schema::dropIfExists('lamtim_agama');
        Schema::dropIfExists('lamtim_kelas');
        Schema::dropIfExists('lamtim_jurusans');
        Schema::dropIfExists('lamtim_sekolahs');
        Schema::dropIfExists('lamtim_tahun_ajarans');
    }
};
