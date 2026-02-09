<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom idSemester di tagihans
        Schema::table('lamtim_tagihans', function (Blueprint $table) {
            $table->uuid('idSemester')->nullable()->after('idSekolah')->comment('Reference ke semester');
            $table->index('idSemester');
        });

        // 2. Migrate data semester string ke idSemester
        $this->migrateSemesterData('lamtim_tagihans');

        // 3. Hapus kolom semester string dan tambah foreign key
        Schema::table('lamtim_tagihans', function (Blueprint $table) {
            $table->dropIndex(['semester']);
            $table->dropColumn('semester');
            
            $table->foreign('idSemester')
                ->references('id')
                ->on('lamtim_semesters')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // 4. Tambah kolom idSemester di invoices
        Schema::table('lamtim_invoices', function (Blueprint $table) {
            $table->uuid('idSemester')->nullable()->after('idSekolah')->comment('Reference ke semester');
            $table->index('idSemester');
        });

        // 5. Migrate data semester string ke idSemester
        $this->migrateSemesterData('lamtim_invoices');

        // 6. Hapus kolom semester string dan tambah foreign key
        Schema::table('lamtim_invoices', function (Blueprint $table) {
            $table->dropIndex(['semester']);
            $table->dropColumn('semester');
            
            $table->foreign('idSemester')
                ->references('id')
                ->on('lamtim_semesters')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // 7. Tambah kolom idSemester di pembayarans
        Schema::table('lamtim_pembayarans', function (Blueprint $table) {
            $table->uuid('idSemester')->nullable()->after('idSekolah')->comment('Reference ke semester');
            $table->index('idSemester');
        });

        // 8. Migrate data semester string ke idSemester
        $this->migrateSemesterData('lamtim_pembayarans');

        // 9. Hapus kolom semester string dan tambah foreign key
        Schema::table('lamtim_pembayarans', function (Blueprint $table) {
            $table->dropIndex(['semester']);
            $table->dropColumn('semester');
            
            $table->foreign('idSemester')
                ->references('id')
                ->on('lamtim_semesters')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Migrate data semester dari string ke idSemester
     */
    private function migrateSemesterData(string $table): void
    {
        // Ensure semester data exists
        $ganjilId = DB::table('lamtim_semesters')->where('kode', 'Ganjil')->value('id');
        $genapId = DB::table('lamtim_semesters')->where('kode', 'Genap')->value('id');

        // Create semester if not exists
        if (!$ganjilId) {
            $ganjilId = (string) \Illuminate\Support\Str::uuid();
            DB::table('lamtim_semesters')->insert([
                'id' => $ganjilId,
                'kode' => 'Ganjil',
                'nama' => 'Semester Ganjil',
                'namaSingkat' => 'Ganjil',
                'isActive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!$genapId) {
            $genapId = (string) \Illuminate\Support\Str::uuid();
            DB::table('lamtim_semesters')->insert([
                'id' => $genapId,
                'kode' => 'Genap',
                'nama' => 'Semester Genap',
                'namaSingkat' => 'Genap',
                'isActive' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Migrate data
        if ($ganjilId) {
            DB::table($table)
                ->where(function($q) {
                    $q->where('semester', 'Ganjil')
                      ->orWhere('semester', 'GANJIL')
                      ->orWhere('semester', 'ganjil');
                })
                ->update(['idSemester' => $ganjilId]);
        }

        if ($genapId) {
            DB::table($table)
                ->where(function($q) {
                    $q->where('semester', 'Genap')
                      ->orWhere('semester', 'GENAP')
                      ->orWhere('semester', 'genap');
                })
                ->update(['idSemester' => $genapId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert tagihans
        Schema::table('lamtim_tagihans', function (Blueprint $table) {
            $table->dropForeign(['idSemester']);
            $table->dropIndex(['idSemester']);
            $table->dropColumn('idSemester');
            $table->string('semester', 20)->nullable()->after('idSekolah');
            $table->index('semester');
        });

        // Revert invoices
        Schema::table('lamtim_invoices', function (Blueprint $table) {
            $table->dropForeign(['idSemester']);
            $table->dropIndex(['idSemester']);
            $table->dropColumn('idSemester');
            $table->string('semester', 20)->nullable()->after('idSekolah');
            $table->index('semester');
        });

        // Revert pembayarans
        Schema::table('lamtim_pembayarans', function (Blueprint $table) {
            $table->dropForeign(['idSemester']);
            $table->dropIndex(['idSemester']);
            $table->dropColumn('idSemester');
            $table->string('semester', 20)->nullable()->after('idSekolah');
            $table->index('semester');
        });
    }
};
