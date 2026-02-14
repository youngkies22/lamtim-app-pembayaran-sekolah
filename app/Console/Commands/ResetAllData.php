<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetAllData extends Command
{
    protected $signature = 'data:reset {--force : Skip confirmation prompt}';

    protected $description = 'Mengosongkan semua data kecuali tabel users dan agama';

    /**
     * Tables that should NOT be truncated.
     */
    protected array $protectedTables = [
        'users',
        'lamtim_agamas',
        'migrations',
        'sessions',
    ];

    public function handle(): int
    {
        $this->warn('');
        $this->warn('╔══════════════════════════════════════════╗');
        $this->warn('║  ⚠️  PERINGATAN: RESET SEMUA DATA!      ║');
        $this->warn('╚══════════════════════════════════════════╝');
        $this->warn('');
        $this->info('Tabel yang DILINDUNGI (tidak dihapus):');
        foreach ($this->protectedTables as $t) {
            $this->line("  🛡️  {$t}");
        }
        $this->warn('');
        $this->error('Semua tabel lain akan DIKOSONGKAN!');
        $this->warn('');

        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda YAKIN ingin menghapus semua data?', false)) {
                $this->info('Dibatalkan.');
                return Command::SUCCESS;
            }

            if (!$this->confirm('Tindakan ini TIDAK BISA dibatalkan. Lanjutkan?', false)) {
                $this->info('Dibatalkan.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();

        try {
            // Get all application tables
            $allTables = collect(DB::select(
                "SELECT tablename FROM pg_tables WHERE schemaname = current_schema()"
            ))->pluck('tablename')->toArray();

            $tablesToTruncate = array_diff($allTables, $this->protectedTables);

            if (empty($tablesToTruncate)) {
                $this->info('Tidak ada tabel yang perlu dikosongkan.');
                return Command::SUCCESS;
            }

            $this->info('Mengosongkan ' . count($tablesToTruncate) . ' tabel...');
            $bar = $this->output->createProgressBar(count($tablesToTruncate));
            $bar->start();

            // Disable FK constraints, truncate, re-enable
            DB::statement('SET session_replication_role = replica;');

            $truncated = [];
            $errors = [];

            foreach ($tablesToTruncate as $table) {
                try {
                    DB::table($table)->truncate();
                    $truncated[] = $table;
                } catch (\Exception $e) {
                    $errors[] = "{$table}: {$e->getMessage()}";
                }
                $bar->advance();
            }

            DB::statement('SET session_replication_role = DEFAULT;');

            $bar->finish();
            $this->newLine(2);

            // Results
            $this->info("✅ Berhasil dikosongkan: " . count($truncated) . " tabel");

            if (!empty($errors)) {
                $this->warn("⚠️  Gagal: " . count($errors) . " tabel");
                foreach ($errors as $err) {
                    $this->error("  • {$err}");
                }
            }

            $this->newLine();
            $this->info('Data berhasil direset! Tabel users dan agama tetap utuh.');

            return empty($errors) ? Command::SUCCESS : Command::FAILURE;
        } catch (\Exception $e) {
            // Make sure FK constraints are re-enabled
            DB::statement('SET session_replication_role = DEFAULT;');
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
