<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\MasterPembayaranRepositoryInterface;
use App\Repositories\MasterPembayaranRepository;
use App\Repositories\Interfaces\TagihanRepositoryInterface;
use App\Repositories\TagihanRepository;
use App\Repositories\Interfaces\PembayaranRepositoryInterface;
use App\Repositories\PembayaranRepository;
use App\Repositories\Interfaces\SiswaRepositoryInterface;
use App\Repositories\SiswaRepository;
use App\Repositories\Interfaces\KelasRepositoryInterface;
use App\Repositories\KelasRepository;
use App\Repositories\Interfaces\RombelRepositoryInterface;
use App\Repositories\RombelRepository;
use App\Repositories\Interfaces\JurusanRepositoryInterface;
use App\Repositories\JurusanRepository;
use App\Repositories\Interfaces\SekolahRepositoryInterface;
use App\Repositories\SekolahRepository;
use App\Repositories\Interfaces\TahunAjaranRepositoryInterface;
use App\Repositories\TahunAjaranRepository;
use App\Repositories\Interfaces\SemesterRepositoryInterface;
use App\Repositories\SemesterRepository;
use App\Repositories\Interfaces\AgamaRepositoryInterface;
use App\Repositories\AgamaRepository;
use App\Models\LamtimJurusan;
use App\Models\LamtimKelas;
use App\Models\LamtimRombel;
use App\Models\LamtimAgama;
use App\Models\LamtimSiswa;
use App\Models\LamtimSekolah;
use App\Models\LamtimMasterPembayaran;
use App\Models\LamtimSemester;
use App\Models\LamtimPembayaran;
use App\Models\LamtimSetting;
use App\Observers\MasterDataObserver;
use App\Repositories\ImportRepository;
use App\Repositories\Interfaces\ImportRepositoryInterface;
use App\Repositories\Interfaces\JenisPembayaranRepositoryInterface;
use App\Repositories\Interfaces\KategoriPembayaranRepositoryInterface;
use App\Repositories\Interfaces\TipePembayaranRepositoryInterface;
use App\Repositories\Interfaces\AlumniAnalysisRepositoryInterface;
use App\Repositories\Interfaces\ActiveStudentAnalysisRepositoryInterface;
use App\Repositories\JenisPembayaranRepository;
use App\Repositories\KategoriPembayaranRepository;
use App\Repositories\TipePembayaranRepository;
use App\Repositories\AlumniAnalysisRepository;
use App\Repositories\ActiveStudentAnalysisRepository;
use App\Services\Interfaces\AcademicIntegrationServiceInterface;
use App\Services\AcademicIntegrationService;
use App\Repositories\Interfaces\JobRepositoryInterface;
use App\Repositories\JobRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            MasterPembayaranRepositoryInterface::class,
            MasterPembayaranRepository::class
        );

        $this->app->bind(
            TagihanRepositoryInterface::class,
            TagihanRepository::class
        );

        $this->app->bind(
            PembayaranRepositoryInterface::class,
            PembayaranRepository::class
        );

        $this->app->bind(
            SiswaRepositoryInterface::class,
            SiswaRepository::class
        );

        $this->app->bind(
            KelasRepositoryInterface::class,
            KelasRepository::class
        );

        $this->app->bind(
            RombelRepositoryInterface::class,
            RombelRepository::class
        );

        $this->app->bind(
            JurusanRepositoryInterface::class,
            JurusanRepository::class
        );

        $this->app->bind(
            SekolahRepositoryInterface::class,
            SekolahRepository::class
        );

        $this->app->bind(
            TahunAjaranRepositoryInterface::class,
            TahunAjaranRepository::class
        );

        $this->app->bind(
            SemesterRepositoryInterface::class,
            SemesterRepository::class
        );

        $this->app->bind(
            AgamaRepositoryInterface::class,
            AgamaRepository::class
        );

        $this->app->bind(
            ImportRepositoryInterface::class,
            ImportRepository::class
        );

        $this->app->bind(
           JenisPembayaranRepositoryInterface::class,
            JenisPembayaranRepository::class
        );

        $this->app->bind(
            KategoriPembayaranRepositoryInterface::class,
            KategoriPembayaranRepository::class
        );

        $this->app->bind(
            TipePembayaranRepositoryInterface::class,
            TipePembayaranRepository::class
        );

        $this->app->bind(
            AlumniAnalysisRepositoryInterface::class,
            AlumniAnalysisRepository::class
        );

        $this->app->bind(
            ActiveStudentAnalysisRepositoryInterface::class,
            ActiveStudentAnalysisRepository::class
        );

        $this->app->bind(
            AcademicIntegrationServiceInterface::class,
            AcademicIntegrationService::class
        );

        $this->app->bind(
            JobRepositoryInterface::class,
            JobRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Observers for Master Data models
        LamtimJurusan::observe(MasterDataObserver::class);
        LamtimKelas::observe(MasterDataObserver::class);
        LamtimRombel::observe(MasterDataObserver::class);
        LamtimAgama::observe(MasterDataObserver::class);
        LamtimSiswa::observe(MasterDataObserver::class);
        LamtimSekolah::observe(MasterDataObserver::class);
        LamtimMasterPembayaran::observe(MasterDataObserver::class);
        LamtimSemester::observe(MasterDataObserver::class);
        LamtimPembayaran::observe(MasterDataObserver::class);
        LamtimSetting::observe(MasterDataObserver::class);
    }
}
