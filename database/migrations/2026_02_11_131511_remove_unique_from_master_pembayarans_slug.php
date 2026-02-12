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
        Schema::table('lamtim_master_pembayarans', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->index('slug'); // Add normal index instead
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_master_pembayarans', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->unique('slug');
        });
    }
};
