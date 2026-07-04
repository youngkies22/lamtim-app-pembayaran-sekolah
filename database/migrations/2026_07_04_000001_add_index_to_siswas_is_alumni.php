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
        Schema::table('lamtim_siswas', function (Blueprint $table) {
            // isAlumni tidak pernah diberi index saat kolomnya ditambahkan,
            // padahal dipakai luas di dashboard, laporan, dan scopeActive()
            // (isActive + isAlumni selalu difilter bersamaan).
            $table->index('isAlumni');
            $table->index(['isActive', 'isAlumni']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_siswas', function (Blueprint $table) {
            $table->dropIndex(['isAlumni']);
            $table->dropIndex(['isActive', 'isAlumni']);
        });
    }
};
