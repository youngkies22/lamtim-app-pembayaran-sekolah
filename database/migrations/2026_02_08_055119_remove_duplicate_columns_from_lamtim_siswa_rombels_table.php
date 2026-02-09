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
        Schema::table('lamtim_siswa_rombels', function (Blueprint $table) {
            // Migration ini untuk backward compatibility jika ada database lama
            // Kolom idSekolah dan idJurusan sudah tidak ada di migration create terbaru
            
            if (Schema::hasColumn('lamtim_siswa_rombels', 'idSekolah')) {
                try {
                    $table->dropIndex(['idSekolah']);
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }
                $table->dropColumn('idSekolah');
            }

            if (Schema::hasColumn('lamtim_siswa_rombels', 'idJurusan')) {
                try {
                    $table->dropIndex(['idJurusan']);
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }
                $table->dropColumn('idJurusan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_siswa_rombels', function (Blueprint $table) {
            $table->uuid('idSekolah')->nullable()->after('idRombel');
            $table->uuid('idJurusan')->nullable()->after('idSekolah');
            
            $table->index('idSekolah');
            $table->index('idJurusan');
        });
    }
};
