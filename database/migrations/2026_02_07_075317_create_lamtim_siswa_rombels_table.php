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
        Schema::create('lamtim_siswa_rombels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('idSiswa');
            $table->uuid('idRombel')->nullable();
            
            $table->timestamps();

            // Indexes for better performance
            $table->index('idSiswa');
            $table->index('idRombel');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_siswa_rombels');
    }
};
