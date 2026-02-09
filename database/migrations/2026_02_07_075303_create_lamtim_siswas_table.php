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
        Schema::create('lamtim_siswas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('idAgama')->nullable();
            $table->string('username', 100)->unique();
            $table->string('nama', 255);
            $table->string('password', 255);
            $table->tinyInteger('jsk')->default(0)->comment('0=Pria, 1=Laki-laki');
            $table->string('nis', 50)->nullable()->unique();
            $table->string('nisn', 50)->nullable()->unique();
            $table->string('qrcode', 255)->nullable();
            $table->string('fotoOsis', 255)->nullable();
            $table->string('fotoProfile', 255)->nullable();
            $table->string('waSiswa', 20)->nullable();
            $table->string('waOrtu', 20)->nullable();
            $table->string('waWali', 20)->nullable();
            $table->tinyInteger('isActive')->default(1)->comment('1=aktif, 0=hapus, 2=off');
            $table->string('tahunAngkatan', 10)->nullable();
            $table->uuid('createdBy')->nullable();
            $table->uuid('updatedBy')->nullable();
            $table->uuid('deletedBy')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('username');
            $table->index('nis');
            $table->index('nisn');
            $table->index('isActive');
            $table->index('tahunAngkatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_siswas');
    }
};
