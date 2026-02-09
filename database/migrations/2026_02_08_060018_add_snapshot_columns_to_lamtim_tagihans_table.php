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
        Schema::table('lamtim_tagihans', function (Blueprint $table) {
            // Snapshot data untuk report/jurnal (data real saat transaksi)
            $table->uuid('idKelas')->nullable()->after('idRombel')->comment('Snapshot: kelas saat tagihan dibuat');
            $table->uuid('idJurusan')->nullable()->after('idKelas')->comment('Snapshot: jurusan saat tagihan dibuat');
            $table->uuid('idSekolah')->nullable()->after('idJurusan')->comment('Snapshot: sekolah saat tagihan dibuat');
            $table->string('semester', 20)->nullable()->after('idSekolah')->comment('Snapshot: semester (Ganjil/Genap) saat tagihan dibuat');
            
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
        Schema::table('lamtim_tagihans', function (Blueprint $table) {
            $table->dropIndex(['idKelas']);
            $table->dropIndex(['idJurusan']);
            $table->dropIndex(['idSekolah']);
            $table->dropIndex(['semester']);
            
            $table->dropColumn(['idKelas', 'idJurusan', 'idSekolah', 'semester']);
        });
    }
};
