<?php

namespace App\Providers;

use App\Jobs\ProductCreated;
use App\Jobs\ProductDeleted;
use App\Jobs\ProductUpdated;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class fAppServiceProvider extends ServiceProvider
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
        App::bindMethod(ProductCreated::class . '@handle', fn($job) => $job->handle());
        App::bindMethod(ProductUpdated::class . '@handle', fn($job) => $job->handle());
        App::bindMethod(ProductDeleted::class . '@handle', fn($job) => $job->handle());
    }
}
