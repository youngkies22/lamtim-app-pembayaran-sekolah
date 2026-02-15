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
        Schema::table('lamtim_pembayarans', function (Blueprint $table) {
            $table->string('sync_status')->nullable()->default('pending')->index();
            $table->timestamp('sync_at')->nullable();
            $table->text('sync_error')->nullable();
        });

        Schema::table('lamtim_tagihans', function (Blueprint $table) {
            $table->string('sync_status')->nullable()->default('pending')->index();
            $table->timestamp('sync_at')->nullable();
            $table->text('sync_error')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_tagihans', function (Blueprint $table) {
            $table->dropColumn(['sync_status', 'sync_at', 'sync_error']);
        });

        Schema::table('lamtim_pembayarans', function (Blueprint $table) {
            $table->dropColumn(['sync_status', 'sync_at', 'sync_error']);
        });
    }
};
