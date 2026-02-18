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
        Schema::table('lamtim_settings', function (Blueprint $table) {
            $table->boolean('job_sync_external_enabled')->default(false);
            $table->boolean('job_sync_siswa_enabled')->default(false);
            $table->boolean('job_push_academic_enabled')->default(false);
            $table->boolean('job_process_import_enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_settings', function (Blueprint $table) {
            $table->dropColumn([
                'job_sync_external_enabled',
                'job_sync_siswa_enabled',
                'job_push_academic_enabled',
                'job_process_import_enabled',
            ]);
        });
    }
};
