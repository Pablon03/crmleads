<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver de scraping de leads
    |--------------------------------------------------------------------------
    | Valores disponibles:
    |   'dummy'     → datos fake (sin API, para pruebas)
    |   'localdata' → Local Business Data API via RapidAPI
    |                 https://rapidapi.com/letscrape-6bRBa3QguO5/api/local-business-data
    |
    | Para añadir un driver personalizado:
    |   1. Crea app/Services/LeadScraping/Drivers/TuDriver.php
    |      implementando LeadScraperInterface
    |   2. Añade el binding en LeadScrapingServiceProvider
    |   3. Establece LEAD_SCRAPER_DRIVER=tu_driver en .env
    */

    'driver' => env('LEAD_SCRAPER_DRIVER', 'dummy'),

    // RapidAPI key (usada por 'localdata' y otros drivers de RapidAPI)
    'api_key' => env('LEAD_SCRAPER_API_KEY'),

    // Host del endpoint de RapidAPI (localdata driver)
    'rapidapi_host' => env('LEAD_SCRAPER_RAPIDAPI_HOST', 'local-business-data.p.rapidapi.com'),

    // Idioma y región para los resultados (localdata driver)
    'language' => env('LEAD_SCRAPER_LANGUAGE', 'es'),
    'region'   => env('LEAD_SCRAPER_REGION', 'es'),

];
