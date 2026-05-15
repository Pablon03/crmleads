<?php

namespace App\Providers;

use App\Models\LeadService;
use App\Observers\LeadServiceObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        LeadService::observe(LeadServiceObserver::class);
    }
}
