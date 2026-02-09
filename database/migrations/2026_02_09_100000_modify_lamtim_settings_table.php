<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk mengubah struktur tabel lamtim_settings
 * Dari key-value store menjadi kolom langsung untuk aplikasi settings
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lamtim_settings', function (Blueprint $table) {
            // Drop kolom key-value jika ada
            if (Schema::hasColumn('lamtim_settings', 'key')) {
                $table->dropIndex(['key']);
                $table->dropColumn('key');
            }
            if (Schema::hasColumn('lamtim_settings', 'value')) {
                $table->dropColumn('value');
            }
            if (Schema::hasColumn('lamtim_settings', 'group')) {
                $table->dropIndex(['group']);
                $table->dropColumn('group');
            }
            
            // Tambahkan kolom baru untuk settings aplikasi
            if (!Schema::hasColumn('lamtim_settings', 'nama_aplikasi')) {
                $table->string('nama_aplikasi', 255)->nullable()->after('id');
            }
            if (!Schema::hasColumn('lamtim_settings', 'logo_aplikasi')) {
                $table->string('logo_aplikasi', 500)->nullable()->after('nama_aplikasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_settings', function (Blueprint $table) {
            // Kembalikan ke struktur key-value
            if (Schema::hasColumn('lamtim_settings', 'nama_aplikasi')) {
                $table->dropColumn('nama_aplikasi');
            }
            if (Schema::hasColumn('lamtim_settings', 'logo_aplikasi')) {
                $table->dropColumn('logo_aplikasi');
            }
            
            if (!Schema::hasColumn('lamtim_settings', 'key')) {
                $table->string('key', 100)->unique()->after('id');
                $table->text('value')->nullable()->after('key');
                $table->string('group', 50)->nullable()->after('value');
                $table->index('key');
                $table->index('group');
            }
        });
    }
};
