<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AcademicSyncPushCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'academic:sync-push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push failed or pending billing and payment data to the academic system';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\Interfaces\AcademicIntegrationServiceInterface $service)
    {
        $this->info('Starting Academic Integration Sync Push...');

        // 1. Sync Tagihans
        $tagihans = \App\Models\LamtimTagihan::where(function ($q) {
            $q->whereNull('sync_status')
                ->orWhere('sync_status', '!=', 'success');
        })->where('isActive', 1)->get();

        $this->info("Found {$tagihans->count()} tagihan(s) to sync.");
        
        $bar = $this->output->createProgressBar($tagihans->count());
        $bar->start();

        foreach ($tagihans as $tagihan) {
            $service->pushTagihan($tagihan);
            $bar->advance();
            usleep(200000); // Increased delay to avoid final 429s
        }

        $bar->finish();
        $this->newLine();

        // 2. Sync Pembayarans
        $pembayarans = \App\Models\LamtimPembayaran::where(function ($q) {
            $q->whereNull('sync_status')
                ->orWhere('sync_status', '!=', 'success');
        })->where('isActive', 1)->get();

        $this->info("Found {$pembayarans->count()} pembayaran(s) to sync.");
        
        $bar = $this->output->createProgressBar($pembayarans->count());
        $bar->start();

        foreach ($pembayarans as $pembayaran) {
            $service->pushPembayaran($pembayaran);
            $bar->advance();
            usleep(50000); // 50ms delay
        }

        $bar->finish();
        $this->newLine();

        $this->info('Sync Push completed.');
    }
}
