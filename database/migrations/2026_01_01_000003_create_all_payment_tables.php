<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated Migration: All Payment Tables.
 *
 * Tables created:
 *  - lamtim_master_pembayarans
 *  - lamtim_tagihans
 *  - lamtim_invoices
 *  - lamtim_pembayarans
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================
        // MASTER PEMBAYARAN
        // ============================================
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
            $table->string('slug')->nullable();
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
            $table->index('slug');
            $table->index('jenisPembayaran');
            $table->index('kategori');
            $table->index('isActive');
            $table->index('idTahunAjaran');
            $table->index('idSekolah');
            $table->index('idJurusan');
            $table->index('idKelas');
        });

        // ============================================
        // TAGIHAN (dengan snapshot columns)
        // ============================================
        Schema::create('lamtim_tagihans', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Reference
            $table->uuid('idSiswa');
            $table->uuid('idMasterPembayaran')->comment('Reference ke master pembayaran');
            $table->uuid('idTahunAjaran')->nullable();
            $table->uuid('idRombel')->nullable();

            // Snapshot columns (data real saat transaksi)
            $table->uuid('idKelas')->nullable()->comment('Snapshot: kelas saat tagihan dibuat');
            $table->uuid('idJurusan')->nullable()->comment('Snapshot: jurusan saat tagihan dibuat');
            $table->uuid('idSekolah')->nullable()->comment('Snapshot: sekolah saat tagihan dibuat');
            $table->uuid('idSemester')->nullable()->comment('Reference ke semester');

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
            $table->index(['idSiswa', 'idMasterPembayaran']);
            $table->index('idKelas');
            $table->index('idJurusan');
            $table->index('idSekolah');
            $table->index('idSemester');
            $table->index('status');
            $table->index('isActive');
            $table->index('bulan');
            $table->index('tanggalJatuhTempo');
        });

        // ============================================
        // INVOICE (dengan snapshot columns)
        // ============================================
        Schema::create('lamtim_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Reference
            $table->uuid('idSiswa');
            $table->uuid('idTagihan')->comment('Reference ke tagihan');
            $table->uuid('idMasterPembayaran')->nullable();
            $table->uuid('idTahunAjaran')->nullable();
            $table->uuid('idRombel')->nullable();

            // Snapshot columns (data real saat transaksi)
            $table->uuid('idKelas')->nullable()->comment('Snapshot: kelas saat invoice dibuat');
            $table->uuid('idJurusan')->nullable()->comment('Snapshot: jurusan saat invoice dibuat');
            $table->uuid('idSekolah')->nullable()->comment('Snapshot: sekolah saat invoice dibuat');
            $table->uuid('idSemester')->nullable()->comment('Reference ke semester');

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
            $table->index('idKelas');
            $table->index('idJurusan');
            $table->index('idSekolah');
            $table->index('idSemester');
            $table->index('noInvoice');
            $table->index('status');
            $table->index('isActive');
            $table->index('tanggalInvoice');
        });

        // ============================================
        // PEMBAYARAN (dengan snapshot columns)
        // ============================================
        Schema::create('lamtim_pembayarans', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Reference
            $table->uuid('idSiswa');
            $table->uuid('idInvoice')->comment('Reference ke invoice');
            $table->uuid('idTagihan')->nullable()->comment('Reference ke tagihan (untuk tracking)');
            $table->uuid('idMasterPembayaran')->nullable();

            // Snapshot columns (data real saat transaksi)
            $table->uuid('idTahunAjaran')->nullable()->comment('Snapshot: tahun ajaran saat pembayaran');
            $table->uuid('idRombel')->nullable()->comment('Snapshot: rombel saat pembayaran');
            $table->uuid('idKelas')->nullable()->comment('Snapshot: kelas saat pembayaran');
            $table->uuid('idJurusan')->nullable()->comment('Snapshot: jurusan saat pembayaran');
            $table->uuid('idSekolah')->nullable()->comment('Snapshot: sekolah saat pembayaran');
            $table->uuid('idSemester')->nullable()->comment('Reference ke semester');

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
            $table->index('idTahunAjaran');
            $table->index('idRombel');
            $table->index('idKelas');
            $table->index('idJurusan');
            $table->index('idSekolah');
            $table->index('idSemester');
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
        Schema::dropIfExists('lamtim_invoices');
        Schema::dropIfExists('lamtim_tagihans');
        Schema::dropIfExists('lamtim_master_pembayarans');
    }
};
