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
        Schema::create('closings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['daily', 'monthly']);
            $table->date('date')->nullable(); // For daily
            $table->unsignedTinyInteger('month')->nullable(); // For monthly
            $table->unsignedSmallInteger('year')->nullable(); // For monthly
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('closed_at')->nullable();
            
            // Changed to foreignUuid because users table uses UUID
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->decimal('total_income', 15, 2)->default(0);
            $table->timestamps();

            // Unique constraints
            // For daily: ensuring only one daily closing per date.
            // Note: date is nullable, but for 'daily' type it should be set.
            // We rely on logic to ensure date is set for daily.
            // Unique index might allow multiple NULLs, so this works for monthly rows (where date is null).
            $table->unique(['date', 'type'], 'unique_daily_closing'); 
            
            // For monthly: ensuring only one monthly closing per month/year.
            $table->unique(['month', 'year', 'type'], 'unique_monthly_closing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closings');
    }
};
