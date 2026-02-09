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
        Schema::table('lamtim_pembayarans', function (Blueprint $table) {
            // Snapshot data untuk report/jurnal (data real saat transaksi)
            $table->uuid('idTahunAjaran')->nullable()->after('idMasterPembayaran')->comment('Snapshot: tahun ajaran saat pembayaran');
            $table->uuid('idRombel')->nullable()->after('idTahunAjaran')->comment('Snapshot: rombel saat pembayaran');
            $table->uuid('idKelas')->nullable()->after('idRombel')->comment('Snapshot: kelas saat pembayaran');
            $table->uuid('idJurusan')->nullable()->after('idKelas')->comment('Snapshot: jurusan saat pembayaran');
            $table->uuid('idSekolah')->nullable()->after('idJurusan')->comment('Snapshot: sekolah saat pembayaran');
            $table->string('semester', 20)->nullable()->after('idSekolah')->comment('Snapshot: semester (Ganjil/Genap) saat pembayaran');
            
            $table->index('idTahunAjaran');
            $table->index('idRombel');
            $table->index('idKelas');
            $table->index('idJurusan');
            $table->index('idSekolah');
            $table->index('semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_pembayarans', function (Blueprint $table) {
            $table->dropIndex(['idTahunAjaran']);
            $table->dropIndex(['idRombel']);
            $table->dropIndex(['idKelas']);
            $table->dropIndex(['idJurusan']);
            $table->dropIndex(['idSekolah']);
            $table->dropIndex(['semester']);
            
            $table->dropColumn(['idTahunAjaran', 'idRombel', 'idKelas', 'idJurusan', 'idSekolah', 'semester']);
        });
    }
};
