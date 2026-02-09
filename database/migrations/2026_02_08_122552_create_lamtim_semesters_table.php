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
        Schema::create('lamtim_semesters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 100)->unique()->comment('Ganjil, Genap');
            $table->string('nama', 255)->comment('Semester Ganjil, Semester Genap');
            $table->string('namaSingkat', 50)->nullable()->comment('Ganjil, Genap');
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=nonaktif');
            $table->timestamps();
            
            $table->index('kode');
            $table->index('isActive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_semesters');
    }
};
