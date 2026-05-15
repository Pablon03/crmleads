<?php

namespace App\Services\LeadScraping;

interface LeadScraperInterface
{
    /**
     * Obtiene negocios según criterios de búsqueda.
     *
     * @return ScrapedBusiness[]
     */
    public function fetchBusinesses(
        string $query,
        string $location,
        int $radiusMeters = 5000,
        int $limit = 20,
        int $pages = 1,
    ): array;
}
