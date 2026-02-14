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

            $truncated = [];
            $errors = [];

            foreach ($tablesToTruncate as $table) {
                try {
                    // Use TRUNCATE CASCADE to handle FK constraints without superuser
                    DB::statement("TRUNCATE TABLE \"{$table}\" RESTART IDENTITY CASCADE;");
                    $truncated[] = $table;
                } catch (\Exception $e) {
                    $errors[] = "{$table}: {$e->getMessage()}";
                }
                $bar->advance();
            }

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
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
