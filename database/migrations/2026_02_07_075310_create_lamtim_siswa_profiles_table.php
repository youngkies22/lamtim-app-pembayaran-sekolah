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
        Schema::create('lamtim_siswa_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('idSiswa');
            
            // Data Pribadi
            $table->string('tpl', 100)->nullable()->comment('Tempat Lahir');
            $table->date('tgl')->nullable()->comment('Tanggal Lahir');
            $table->string('kk', 50)->nullable()->comment('Nomor KK');
            $table->string('nik', 50)->nullable()->comment('NIK');
            $table->integer('tinggiBadan')->nullable();
            $table->string('transport', 100)->nullable();
            $table->string('hobi', 255)->nullable();
            $table->string('jarak', 50)->nullable();
            
            // Alamat
            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->string('desa', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kabupaten', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            
            // Program Bantuan
            $table->boolean('pkh')->default(false)->comment('Program Keluarga Harapan');
            $table->boolean('kip')->default(false)->comment('Kartu Indonesia Pintar');
            
            // Data SMP
            $table->string('asalSmp', 255)->nullable();
            $table->string('npsnSmp', 50)->nullable();
            $table->text('alamatSmp')->nullable();
            
            // Data Ayah
            $table->string('ayah', 255)->nullable();
            $table->string('ayahTpl', 100)->nullable();
            $table->date('ayahTgl')->nullable();
            $table->string('ayahNik', 50)->nullable();
            $table->string('ayahPendidikan', 100)->nullable();
            $table->text('ayahAlamat')->nullable();
            
            // Data Ibu
            $table->string('ibu', 255)->nullable();
            $table->string('ibuTpl', 100)->nullable();
            $table->date('ibuTgl')->nullable();
            $table->string('ibuNik', 50)->nullable();
            $table->string('ibuPendidikan', 100)->nullable();
            $table->text('ibuAlamat')->nullable();
            
            // Data Wali
            $table->string('wali', 255)->nullable();
            $table->string('waliTpl', 100)->nullable();
            $table->date('waliTgl')->nullable();
            $table->string('waliNik', 50)->nullable();
            $table->string('waliPendidikan', 100)->nullable();
            $table->text('waliAlamat')->nullable();
            
            $table->timestamps();

            // Index for better performance
            $table->index('idSiswa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_siswa_profiles');
    }
};
