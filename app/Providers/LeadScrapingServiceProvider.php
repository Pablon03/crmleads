<?php

namespace App\Providers;

use App\Services\LeadScraping\Drivers\DummyDriver;
use App\Services\LeadScraping\Drivers\SerpApiDriver;
use App\Services\LeadScraping\Drivers\LocalBusinessDataDriver;
use App\Services\LeadScraping\LeadScraperInterface;
use Illuminate\Support\ServiceProvider;

class LeadScrapingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LeadScraperInterface::class, function () {
            $driver = config('lead_scraping.driver', 'dummy');

            return match ($driver) {
                'dummy'      => new DummyDriver(),
                'serpapi' => new SerpApiDriver(config('lead_scraping.api_key')),
                'localdata'  => new LocalBusinessDataDriver(
                    apiKey:   config('lead_scraping.api_key'),
                    host:     config('lead_scraping.rapidapi_host', 'local-business-data.p.rapidapi.com'),
                    language: config('lead_scraping.language', 'es'),
                    region:   config('lead_scraping.region', 'es'),
                ),
                default      => throw new \InvalidArgumentException("Driver de scraping desconocido: [{$driver}]"),
            };
        });
    }
}
