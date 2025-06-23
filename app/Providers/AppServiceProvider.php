<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Artisan;

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
    

public function boot()
{
    if (!file_exists(public_path('storage'))) {
        try {
            Artisan::call('storage:link');
            Log::info('✅ Symlink created successfully.');
        } catch (\Exception $e) {
            Log::error('❌ Storage link creation failed: ' . $e->getMessage());
        }
    }
}

}
