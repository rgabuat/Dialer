<?php

namespace App\Providers;

use App\Models\User;
use Livewire\Livewire;
use App\Models\UsersMeta;
use App\Observers\UserObserver;
use App\Observers\UserMetaObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        $this->app->booted(function () {
            $basePath = request()->getBasePath();
            Livewire::setScriptRoute(function ($handle) use ($basePath) {
               
                // 👇 HARD-CODE FIRST TO VERIFY
                return Route::get(
                    $basePath . '/livewire/livewire.js',
                    $handle
                );
            });

            Livewire::setUpdateRoute(function ($handle) use ($basePath) {
                return Route::post($basePath . '/livewire/update', $handle);
            });

            URL::forceScheme('https');

        });
    }
}
