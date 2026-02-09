<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lamtim_tahun_ajarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 100)->unique();
            $table->string('slag', 100);
            $table->string('nama', 255);
            $table->tinyInteger('isActive')->default(0)->comment('1=aktif, 0=nonaktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_tahun_ajarans');
    }
};
