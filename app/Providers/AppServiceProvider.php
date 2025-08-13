<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SubPart; // <-- TAMBAHKAN BARIS INI
use App\Observers\SubPartObserver; // <-- TAMBAHKAN BARIS INI

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // Daftarkan observer agar aktif saat aplikasi berjalan
        SubPart::observe(SubPartObserver::class);
    }
}
