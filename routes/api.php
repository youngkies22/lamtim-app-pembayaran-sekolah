<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterPembayaranController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SiswaRombelController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\RombelController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\AgamaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JenisPembayaranController;
use App\Http\Controllers\KategoriPembayaranController;
use App\Http\Controllers\TipePembayaranController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CacheManagerController;

// Public Auth Routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Protected Auth Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::match(['put', 'post'], '/profile', [AuthController::class, 'updateProfile'])->name('api.profile.update');
});

  // Public Routes
  Route::get('config', [ConfigController::class, 'index'])->name('api.config');
  Route::get('public/settings', [ConfigController::class, 'publicSettings'])->name('api.public.settings');
  Route::get('public/sekolah', [ConfigController::class, 'publicSekolah'])->name('api.public.sekolah');

  // Protected Routes (require authentication)
  Route::middleware(['auth:sanctum'])->group(function () {
      // User Management Routes - Only Admin
      Route::prefix('users')->middleware('role:1')->group(function () {
          Route::get('/', [UserController::class, 'index'])->name('api.users.index');
          Route::get('/{id}', [UserController::class, 'show'])->name('api.users.show');
          Route::post('/', [UserController::class, 'store'])->name('api.users.store');
          Route::put('/{id}', [UserController::class, 'update'])->name('api.users.update');
          Route::delete('/{id}', [UserController::class, 'destroy'])->name('api.users.destroy');
          Route::post('/{id}/toggle-active', [UserController::class, 'toggleActive'])->name('api.users.toggle-active');
      });

      // Dashboard Routes
      Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');
      Route::get('dashboard/recent-payments', [DashboardController::class, 'recentPayments'])->name('api.dashboard.recent-payments');

    // Master Data Routes
    Route::prefix('master-data')->group(function () {
        Route::get('sekolah/datatable', [SekolahController::class, 'datatable'])->name('api.sekolah.datatable');
        Route::get('sekolah/select', [SekolahController::class, 'select'])->name('api.sekolah.select');
        Route::apiResource('sekolah', SekolahController::class);

        Route::get('jurusan/datatable', [JurusanController::class, 'datatable'])->name('api.jurusan.datatable');
        Route::get('jurusan/select', [JurusanController::class, 'select'])->name('api.jurusan.select');
        Route::apiResource('jurusan', JurusanController::class);

        Route::get('kelas/datatable', [KelasController::class, 'datatable'])->name('api.kelas.datatable');
        Route::get('kelas/select', [KelasController::class, 'select'])->name('api.kelas.select');
        Route::apiResource('kelas', KelasController::class);

        Route::get('rombel/datatable', [RombelController::class, 'datatable'])->name('api.rombel.datatable');
        Route::get('rombel/select', [RombelController::class, 'select'])->name('api.rombel.select');
        Route::apiResource('rombel', RombelController::class);

        Route::get('tahun-ajaran/datatable', [TahunAjaranController::class, 'datatable'])->name('api.tahun-ajaran.datatable');
        Route::apiResource('tahun-ajaran', TahunAjaranController::class);
        Route::post('tahun-ajaran/{id}/set-active', [TahunAjaranController::class, 'setActive'])->name('api.tahun-ajaran.set-active');

        Route::get('semester/datatable', [SemesterController::class, 'datatable'])->name('api.semester.datatable');
        Route::apiResource('semester', SemesterController::class);
        Route::post('semester/{id}/set-active', [SemesterController::class, 'setActive'])->name('api.semester.set-active');

        Route::get('agama/datatable', [AgamaController::class, 'datatable'])->name('api.agama.datatable');
        Route::apiResource('agama', AgamaController::class);

        // Jenis & Kategori & Tipe Pembayaran
        // Read access for all authenticated users, write access only for admin and operator
        Route::get('jenis-pembayaran', [JenisPembayaranController::class, 'index'])->name('api.jenis-pembayaran.index');
        Route::get('jenis-pembayaran/{id}', [JenisPembayaranController::class, 'show'])->name('api.jenis-pembayaran.show');
        Route::post('jenis-pembayaran', [JenisPembayaranController::class, 'store'])->middleware('role:1,2')->name('api.jenis-pembayaran.store');
        Route::put('jenis-pembayaran/{id}', [JenisPembayaranController::class, 'update'])->middleware('role:1,2')->name('api.jenis-pembayaran.update');
        Route::delete('jenis-pembayaran/{id}', [JenisPembayaranController::class, 'destroy'])->middleware('role:1,2')->name('api.jenis-pembayaran.destroy');

        Route::get('kategori-pembayaran', [KategoriPembayaranController::class, 'index'])->name('api.kategori-pembayaran.index');
        Route::get('kategori-pembayaran/{id}', [KategoriPembayaranController::class, 'show'])->name('api.kategori-pembayaran.show');
        Route::post('kategori-pembayaran', [KategoriPembayaranController::class, 'store'])->middleware('role:1,2')->name('api.kategori-pembayaran.store');
        Route::put('kategori-pembayaran/{id}', [KategoriPembayaranController::class, 'update'])->middleware('role:1,2')->name('api.kategori-pembayaran.update');
        Route::delete('kategori-pembayaran/{id}', [KategoriPembayaranController::class, 'destroy'])->middleware('role:1,2')->name('api.kategori-pembayaran.destroy');

        Route::get('tipe-pembayaran', [TipePembayaranController::class, 'index'])->name('api.tipe-pembayaran.index');
        Route::get('tipe-pembayaran/{id}', [TipePembayaranController::class, 'show'])->name('api.tipe-pembayaran.show');
        Route::post('tipe-pembayaran', [TipePembayaranController::class, 'store'])->middleware('role:1,2')->name('api.tipe-pembayaran.store');
        Route::put('tipe-pembayaran/{id}', [TipePembayaranController::class, 'update'])->middleware('role:1,2')->name('api.tipe-pembayaran.update');
        Route::delete('tipe-pembayaran/{id}', [TipePembayaranController::class, 'destroy'])->middleware('role:1,2')->name('api.tipe-pembayaran.destroy');
    });

    // Siswa Routes - Read for all, write only for admin and operator
    Route::get('siswa/datatable', [SiswaController::class, 'datatable'])->name('api.siswa.datatable');
    Route::get('siswa/select', [SiswaController::class, 'select'])->name('api.siswa.select');
    Route::get('siswa', [SiswaController::class, 'index'])->name('api.siswa.index');
    Route::get('siswa/{id}', [SiswaController::class, 'show'])->name('api.siswa.show');
    Route::post('siswa', [SiswaController::class, 'store'])->middleware('role:1,2')->name('api.siswa.store');
    Route::put('siswa/{id}', [SiswaController::class, 'update'])->middleware('role:1,2')->name('api.siswa.update');
    Route::post('siswa/mark-alumni', [SiswaController::class, 'markAsAlumni'])->middleware('role:1,2')->name('api.siswa.mark-alumni');
    Route::delete('siswa/{id}', [SiswaController::class, 'destroy'])->middleware('role:1,2')->name('api.siswa.destroy');

    // Siswa Rombel Mapping Routes - Read for all, write only for admin and operator
    Route::get('siswa-rombel/unmapped', [SiswaRombelController::class, 'getUnmappedStudents'])->name('api.siswa-rombel.unmapped');
    Route::get('siswa-rombel/datatable', [SiswaRombelController::class, 'datatable'])->name('api.siswa-rombel.datatable');
    Route::get('siswa-rombel', [SiswaRombelController::class, 'index'])->name('api.siswa-rombel.index');
    Route::get('siswa-rombel/{id}', [SiswaRombelController::class, 'show'])->name('api.siswa-rombel.show');
    Route::post('siswa-rombel/promote', [SiswaRombelController::class, 'promote'])->middleware('role:1,2')->name('api.siswa-rombel.promote');
    Route::post('siswa-rombel/batch', [SiswaRombelController::class, 'batchStore'])->middleware('role:1,2')->name('api.siswa-rombel.batch');
    Route::post('siswa-rombel', [SiswaRombelController::class, 'store'])->middleware('role:1,2')->name('api.siswa-rombel.store');
    Route::put('siswa-rombel/{id}', [SiswaRombelController::class, 'update'])->middleware('role:1,2')->name('api.siswa-rombel.update');
    Route::delete('siswa-rombel/{id}', [SiswaRombelController::class, 'destroy'])->middleware('role:1,2')->name('api.siswa-rombel.destroy');

    // Master Pembayaran Routes - Read for all, write only for admin and operator
    Route::get('master-pembayaran/datatable', [MasterPembayaranController::class, 'datatable'])->name('api.master-pembayaran.datatable');
    Route::get('master-pembayaran/select', [MasterPembayaranController::class, 'select'])->name('api.master-pembayaran.select');
    Route::get('master-pembayaran', [MasterPembayaranController::class, 'index'])->name('api.master-pembayaran.index');
    Route::get('master-pembayaran/{id}', [MasterPembayaranController::class, 'show'])->name('api.master-pembayaran.show');
    Route::post('master-pembayaran', [MasterPembayaranController::class, 'store'])->middleware('role:1,2')->name('api.master-pembayaran.store');
    Route::put('master-pembayaran/{id}', [MasterPembayaranController::class, 'update'])->middleware('role:1,2')->name('api.master-pembayaran.update');
    Route::delete('master-pembayaran/{id}', [MasterPembayaranController::class, 'destroy'])->middleware('role:1,2')->name('api.master-pembayaran.destroy');

    // Tagihan Routes - Read for all, write only for admin and operator
    Route::get('tagihan/datatable', [TagihanController::class, 'datatable'])->name('api.tagihan.datatable');
    Route::get('tagihan/siswa/{idSiswa}', [TagihanController::class, 'getBySiswa'])->name('api.tagihan.by-siswa');
    Route::get('tagihan', [TagihanController::class, 'index'])->name('api.tagihan.index');
    Route::get('tagihan/{id}', [TagihanController::class, 'show'])->name('api.tagihan.show');
    Route::post('tagihan', [TagihanController::class, 'store'])->middleware('role:1,2')->name('api.tagihan.store');
    Route::post('tagihan/generate-batch', [TagihanController::class, 'generateBatch'])->middleware('role:1,2')->name('api.tagihan.generate-batch');
    Route::put('tagihan/{id}', [TagihanController::class, 'update'])->middleware('role:1,2')->name('api.tagihan.update');
    Route::delete('tagihan/{id}', [TagihanController::class, 'destroy'])->middleware('role:1,2')->name('api.tagihan.destroy');

    // Invoice Routes - Read for all, write only for admin and operator
    Route::get('invoice/stats', [InvoiceController::class, 'stats'])->name('api.invoice.stats');
    Route::get('invoice/datatable', [InvoiceController::class, 'datatable'])->name('api.invoice.datatable');
    Route::get('invoice/export', [InvoiceController::class, 'export'])->name('api.invoice.export');
    Route::get('invoice', [InvoiceController::class, 'index'])->name('api.invoice.index');
    Route::get('invoice/{id}', [InvoiceController::class, 'show'])->name('api.invoice.show');
      // Closing Management Routes
      Route::get('closing', [App\Http\Controllers\ClosingController::class, 'index'])->name('api.closing.index');
      Route::get('closing/check', [App\Http\Controllers\ClosingController::class, 'checkStatus'])->name('api.closing.check');
      Route::post('closing/daily', [App\Http\Controllers\ClosingController::class, 'storeDaily'])->name('api.closing.store-daily');
      Route::post('closing/monthly', [App\Http\Controllers\ClosingController::class, 'storeMonthly'])->name('api.closing.store-monthly');
      Route::post('closing/reopen', [App\Http\Controllers\ClosingController::class, 'reopen'])->name('api.closing.reopen');

    // Invoice Routes - Read for all, write only for admin and operator
    // Apply check.closing middleware to write operations. Dates are in 'tanggalInvoice'
    Route::middleware(['check.closing:tanggalInvoice'])->group(function () {
        Route::post('invoice', [InvoiceController::class, 'store'])->middleware('role:1,2')->name('api.invoice.store');
        Route::put('invoice/{id}', [InvoiceController::class, 'update'])->middleware('role:1,2')->name('api.invoice.update');
        Route::delete('invoice/{id}', [InvoiceController::class, 'destroy'])->middleware('role:1,2')->name('api.invoice.destroy');
    });
    
    Route::get('invoice/stats', [InvoiceController::class, 'stats'])->name('api.invoice.stats');
    Route::get('invoice/datatable', [InvoiceController::class, 'datatable'])->name('api.invoice.datatable');
    Route::get('invoice/export', [InvoiceController::class, 'export'])->name('api.invoice.export');
    Route::get('invoice', [InvoiceController::class, 'index'])->name('api.invoice.index');
    Route::get('invoice/{id}', [InvoiceController::class, 'show'])->name('api.invoice.show');

    // Pembayaran Routes - Read for all, proses/verify only for admin and operator
    // Apply check.closing middleware. Dates are in 'tanggalBayar'
    Route::middleware(['check.closing:tanggalBayar'])->group(function () {
        Route::post('pembayaran/proses', [PembayaranController::class, 'prosesPembayaran'])->middleware('role:1,2')->name('api.pembayaran.proses');
        Route::post('pembayaran/{id}/verify', [PembayaranController::class, 'verify'])->middleware('role:1,2')->name('api.pembayaran.verify');
        Route::post('pembayaran/{id}/cancel', [PembayaranController::class, 'cancel'])->middleware('role:1,2')->name('api.pembayaran.cancel');
        Route::post('pembayaran', [PembayaranController::class, 'store'])->middleware('role:1,2')->name('api.pembayaran.store');
        Route::put('pembayaran/{id}', [PembayaranController::class, 'update'])->middleware('role:1,2')->name('api.pembayaran.update');
        Route::delete('pembayaran/{id}', [PembayaranController::class, 'destroy'])->middleware('role:1,2')->name('api.pembayaran.destroy');
    });

    Route::get('pembayaran/stats', [PembayaranController::class, 'stats'])->name('api.pembayaran.stats');
    Route::get('pembayaran/datatable', [PembayaranController::class, 'datatable'])->name('api.pembayaran.datatable');
    Route::get('pembayaran/export', [PembayaranController::class, 'export'])->name('api.pembayaran.export');
    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('api.pembayaran.index');
    Route::get('pembayaran/{id}', [PembayaranController::class, 'show'])->name('api.pembayaran.show');

      // Import Routes
      Route::prefix('import')->group(function () {
          Route::post('upload', [ImportController::class, 'upload'])->name('api.import.upload');
          Route::get('progress/{id}', [ImportController::class, 'progress'])->name('api.import.progress');
          Route::get('template', [ImportController::class, 'downloadTemplate'])->name('api.import.template');
          Route::get('error/{id}', [ImportController::class, 'downloadErrorFile'])->name('api.import.error');
          Route::get('logs', [ImportController::class, 'index'])->name('api.import.logs');
      });

      // Report Routes
      Route::prefix('reports')->group(function () {
          Route::get('rombel', [ReportController::class, 'rombelReport'])->name('api.reports.rombel');
          Route::get('rombel/headers', [ReportController::class, 'rombelReportHeaders'])->name('api.reports.rombel.headers');
          Route::get('rombel/stats', [ReportController::class, 'rombelReportStats'])->name('api.reports.rombel.stats');
          Route::get('rombel/export', [ReportController::class, 'exportRombelReport'])->name('api.reports.rombel.export');
          Route::get('siswa/export', [ReportController::class, 'exportSiswaReport'])->name('api.reports.siswa.export');
          Route::get('analytics-stats', [ReportController::class, 'analyticsStats'])->name('api.reports.analytics-stats');
      });

      // Settings Routes - Admin Only
      Route::middleware('role:1')->group(function () {
          Route::get('settings', [SettingController::class, 'index'])->name('api.settings.index');
          Route::post('settings', [SettingController::class, 'store'])->name('api.settings.store');
          Route::get('settings/{id}', [SettingController::class, 'show'])->name('api.settings.show');
          Route::put('settings/{id}', [SettingController::class, 'update'])->name('api.settings.update');
          Route::delete('settings/{id}', [SettingController::class, 'destroy'])->name('api.settings.destroy');
      
          // Trash Routes - Admin Only
          Route::get('trash', [\App\Http\Controllers\TrashController::class, 'index'])->name('api.trash.index');
          Route::post('trash/{id}/restore', [\App\Http\Controllers\TrashController::class, 'restore'])->name('api.trash.restore');
          Route::delete('trash/{id}/force-delete', [\App\Http\Controllers\TrashController::class, 'forceDelete'])->name('api.trash.force-delete');
          Route::post('trash/empty', [\App\Http\Controllers\TrashController::class, 'emptyTrash'])->name('api.trash.empty');
      });

      // External Sync Routes - Admin Only
      Route::prefix('sync')->middleware('role:1,2')->group(function () {
          Route::post('/run', [\App\Http\Controllers\ExternalSyncController::class, 'run'])->name('api.sync.run');
          Route::get('/status', [\App\Http\Controllers\ExternalSyncController::class, 'status'])->name('api.sync.status');
          Route::get('/test-connection', [\App\Http\Controllers\ExternalSyncController::class, 'testConnection'])->name('api.sync.test-connection');
          
          // Siswa chunked sync
          Route::post('/siswa/download', [\App\Http\Controllers\ExternalSyncController::class, 'downloadSiswa'])->name('api.sync.siswa.download');
          Route::post('/siswa/process-chunk', [\App\Http\Controllers\ExternalSyncController::class, 'processSiswaChunk'])->name('api.sync.siswa.process-chunk');
          Route::post('/siswa/cleanup', [\App\Http\Controllers\ExternalSyncController::class, 'cleanupSiswa'])->name('api.sync.siswa.cleanup');
      });

      // Backup Routes - Admin & Operator
      Route::prefix('backups')->middleware('role:1,2')->group(function () {
          Route::get('/', [\App\Http\Controllers\BackupController::class, 'index'])->name('api.backups.index');
          Route::post('/', [\App\Http\Controllers\BackupController::class, 'store'])->name('api.backups.store');
          Route::get('/{filename}/download', [\App\Http\Controllers\BackupController::class, 'download'])->name('api.backups.download');
          Route::delete('/{filename}', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('api.backups.destroy');
      });

      // Cache Management Routes - Admin Only
      Route::prefix('cache-manager')->middleware('role:1')->group(function () {
          Route::get('/status', [CacheManagerController::class, 'getStatus']);
          Route::post('/clear-laravel', [CacheManagerController::class, 'clearLaravelCache']);
          Route::post('/clear-redis', [CacheManagerController::class, 'clearRedisCache']);
          Route::post('/optimize', [CacheManagerController::class, 'optimizeCache']);
      });
  });
