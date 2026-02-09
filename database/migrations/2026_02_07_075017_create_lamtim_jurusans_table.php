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
        Schema::create('lamtim_jurusans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('idSekolah');
            $table->string('kode', 50);
            $table->string('nama', 255);
            $table->timestamps();

            $table->foreign('idSekolah')
                ->references('id')
                ->on('lamtim_sekolahs')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_jurusans');
    }
};
