<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add all foreign key constraints for PostgreSQL compatibility
     */
    public function up(): void
    {
        // Foreign keys for lamtim_siswas
        Schema::table('lamtim_siswas', function (Blueprint $table) {
            $table->foreign('idAgama')
                ->references('id')
                ->on('lamtim_agama')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('createdBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('updatedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('deletedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // Foreign keys for lamtim_siswa_profiles
        Schema::table('lamtim_siswa_profiles', function (Blueprint $table) {
            $table->foreign('idSiswa')
                ->references('id')
                ->on('lamtim_siswas')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        // Foreign keys for lamtim_siswa_rombels
        Schema::table('lamtim_siswa_rombels', function (Blueprint $table) {
            $table->foreign('idSiswa')
                ->references('id')
                ->on('lamtim_siswas')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('idRombel')
                ->references('id')
                ->on('lamtim_rombels')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // Foreign keys for lamtim_rombels (idKelas)
        Schema::table('lamtim_rombels', function (Blueprint $table) {
            $table->foreign('idKelas')
                ->references('id')
                ->on('lamtim_kelas')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // Foreign keys for lamtim_master_pembayarans
        Schema::table('lamtim_master_pembayarans', function (Blueprint $table) {
            $table->foreign('idTahunAjaran')
                ->references('id')
                ->on('lamtim_tahun_ajarans')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('idSekolah')
                ->references('id')
                ->on('lamtim_sekolahs')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('idJurusan')
                ->references('id')
                ->on('lamtim_jurusans')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('idRombel')
                ->references('id')
                ->on('lamtim_rombels')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('idKelas')
                ->references('id')
                ->on('lamtim_kelas')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('createdBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('updatedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('deletedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // Foreign keys for lamtim_tagihans
        Schema::table('lamtim_tagihans', function (Blueprint $table) {
            $table->foreign('idSiswa')
                ->references('id')
                ->on('lamtim_siswas')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('idMasterPembayaran')
                ->references('id')
                ->on('lamtim_master_pembayarans')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('idTahunAjaran')
                ->references('id')
                ->on('lamtim_tahun_ajarans')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('idRombel')
                ->references('id')
                ->on('lamtim_rombels')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('createdBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('updatedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('deletedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // Foreign keys for lamtim_invoices
        Schema::table('lamtim_invoices', function (Blueprint $table) {
            $table->foreign('idSiswa')
                ->references('id')
                ->on('lamtim_siswas')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('idTagihan')
                ->references('id')
                ->on('lamtim_tagihans')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('idMasterPembayaran')
                ->references('id')
                ->on('lamtim_master_pembayarans')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('idTahunAjaran')
                ->references('id')
                ->on('lamtim_tahun_ajarans')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('idRombel')
                ->references('id')
                ->on('lamtim_rombels')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('createdBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('updatedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('deletedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // Foreign keys for lamtim_pembayarans
        Schema::table('lamtim_pembayarans', function (Blueprint $table) {
            $table->foreign('idSiswa')
                ->references('id')
                ->on('lamtim_siswas')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('idInvoice')
                ->references('id')
                ->on('lamtim_invoices')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('idTagihan')
                ->references('id')
                ->on('lamtim_tagihans')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('idMasterPembayaran')
                ->references('id')
                ->on('lamtim_master_pembayarans')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('verifiedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('createdBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('updatedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('deletedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys for lamtim_pembayarans
        Schema::table('lamtim_pembayarans', function (Blueprint $table) {
            $table->dropForeign(['idSiswa']);
            $table->dropForeign(['idInvoice']);
            $table->dropForeign(['idTagihan']);
            $table->dropForeign(['idMasterPembayaran']);
            $table->dropForeign(['verifiedBy']);
            $table->dropForeign(['createdBy']);
            $table->dropForeign(['updatedBy']);
            $table->dropForeign(['deletedBy']);
        });

        // Drop foreign keys for lamtim_invoices
        Schema::table('lamtim_invoices', function (Blueprint $table) {
            $table->dropForeign(['idSiswa']);
            $table->dropForeign(['idTagihan']);
            $table->dropForeign(['idMasterPembayaran']);
            $table->dropForeign(['idTahunAjaran']);
            $table->dropForeign(['idRombel']);
            $table->dropForeign(['createdBy']);
            $table->dropForeign(['updatedBy']);
            $table->dropForeign(['deletedBy']);
        });

        // Drop foreign keys for lamtim_tagihans
        Schema::table('lamtim_tagihans', function (Blueprint $table) {
            $table->dropForeign(['idSiswa']);
            $table->dropForeign(['idMasterPembayaran']);
            $table->dropForeign(['idTahunAjaran']);
            $table->dropForeign(['idRombel']);
            $table->dropForeign(['createdBy']);
            $table->dropForeign(['updatedBy']);
            $table->dropForeign(['deletedBy']);
        });

        // Drop foreign keys for lamtim_master_pembayarans
        Schema::table('lamtim_master_pembayarans', function (Blueprint $table) {
            $table->dropForeign(['idTahunAjaran']);
            $table->dropForeign(['idSekolah']);
            $table->dropForeign(['idJurusan']);
            $table->dropForeign(['idRombel']);
            $table->dropForeign(['idKelas']);
            $table->dropForeign(['createdBy']);
            $table->dropForeign(['updatedBy']);
            $table->dropForeign(['deletedBy']);
        });

        // Drop foreign keys for lamtim_rombels (idKelas)
        Schema::table('lamtim_rombels', function (Blueprint $table) {
            $table->dropForeign(['idKelas']);
        });

        // Drop foreign keys for lamtim_siswa_rombels
        Schema::table('lamtim_siswa_rombels', function (Blueprint $table) {
            $table->dropForeign(['idSiswa']);
            $table->dropForeign(['idRombel']);
        });

        // Drop foreign keys for lamtim_siswa_profiles
        Schema::table('lamtim_siswa_profiles', function (Blueprint $table) {
            $table->dropForeign(['idSiswa']);
        });

        // Drop foreign keys for lamtim_siswas
        Schema::table('lamtim_siswas', function (Blueprint $table) {
            $table->dropForeign(['idAgama']);
            $table->dropForeign(['createdBy']);
            $table->dropForeign(['updatedBy']);
            $table->dropForeign(['deletedBy']);
        });
    }
};
