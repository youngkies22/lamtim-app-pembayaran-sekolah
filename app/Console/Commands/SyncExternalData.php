<?php

namespace App\Console\Commands;

use App\Services\ExternalSyncService;
use Illuminate\Console\Command;

class SyncExternalData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sync:external-data
                            {--entity= : Sync specific entity (tahun_ajaran, semester, sekolah, kelas, jurusan, rombel, siswa)}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     */
    protected $description = 'Sinkronisasi data dari external API ke database lokal';

    public function __construct(
        protected ExternalSyncService $syncService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $entity = $this->option('entity');
        $syncOrder = config('external_api.sync_order', []);

        if ($entity && !in_array($entity, $syncOrder)) {
            $this->error("Entity '{$entity}' tidak valid. Pilihan: " . implode(', ', $syncOrder));
            return Command::FAILURE;
        }

        $entities = $entity ? [$entity] : $syncOrder;

        $this->info('');
        $this->info('╔══════════════════════════════════════╗');
        $this->info('║   Sinkronisasi Data External API     ║');
        $this->info('╚══════════════════════════════════════╝');
        $this->info('');
        $this->info('Entity yang akan disinkronisasi:');
        foreach ($entities as $e) {
            $this->line("  • {$e}");
        }
        $this->info('');

        if (!$this->option('force') && !$this->confirm('Lanjutkan sinkronisasi?', true)) {
            $this->warn('Sinkronisasi dibatalkan.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $overallBar = $this->output->createProgressBar(count($entities));
        $overallBar->setFormat(" %current%/%max% [%bar%] %percent:3s%% — %message%");
        $overallBar->setMessage('Memulai...');
        $overallBar->start();

        $results = $this->syncService->sync($entity, function ($entityName, $current, $total) use ($overallBar) {
            $overallBar->setMessage("Sync {$entityName}: {$current}/{$total}");
        });

        $overallBar->setMessage('Selesai!');
        $overallBar->finish();
        $this->newLine(2);

        // Display results table
        $this->info('═══ Hasil Sinkronisasi ═══');
        $this->newLine();

        $tableData = [];
        $hasErrors = false;

        foreach ($results as $entityName => $result) {
            $status = match ($result['status'] ?? 'unknown') {
                'completed' => '<fg=green>✓ Selesai</>',
                'completed_with_errors' => '<fg=yellow>⚠ Sebagian</>',
                'failed' => '<fg=red>✗ Gagal</>',
                'skipped' => '<fg=gray>⊘ Dilewati</>',
                default => $result['status'] ?? 'unknown',
            };

            $tableData[] = [
                $entityName,
                $status,
                $result['inserted'] ?? 0,
                $result['updated'] ?? 0,
                $result['soft_deleted'] ?? 0,
                $result['failed'] ?? 0,
            ];

            if (!empty($result['errors'])) {
                $hasErrors = true;
            }
        }

        $this->table(
            ['Entity', 'Status', 'Ditambahkan', 'Diperbarui', 'Dinonaktifkan', 'Gagal'],
            $tableData
        );

        // Show errors if any
        if ($hasErrors) {
            $this->newLine();
            $this->warn('═══ Detail Error ═══');
            foreach ($results as $entityName => $result) {
                if (!empty($result['errors'])) {
                    foreach ($result['errors'] as $error) {
                        $this->error("  [{$entityName}] {$error}");
                    }
                }
            }
        }

        $this->newLine();

        // Determine exit code
        $totalFailed = collect($results)->sum('failed');
        $totalSuccess = collect($results)->sum('inserted') + collect($results)->sum('updated');

        if ($totalFailed > 0 && $totalSuccess === 0) {
            $this->error('Sinkronisasi gagal total.');
            return 2;
        }

        if ($totalFailed > 0) {
            $this->warn('Sinkronisasi selesai dengan beberapa error.');
            return Command::FAILURE;
        }

        $this->info('Sinkronisasi berhasil diselesaikan!');
        return Command::SUCCESS;
    }
}
