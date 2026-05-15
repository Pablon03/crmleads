<?php
namespace App\Services\LeadScraping\Drivers;

use App\Services\LeadScraping\LeadScraperInterface;
use App\Services\LeadScraping\ScrapedBusiness;
use Illuminate\Support\Facades\Http;

class SerpApiDriver implements LeadScraperInterface
{
    public function __construct(private string $apiKey) {}

    public function fetchBusinesses(
        string $query,
        string $location,
        int $radiusMeters = 5000,
        int $limit = 20,
        int $pages = 1,
    ): array {
        $response = Http::get('https://serpapi.com/search.json', [
            'engine'  => 'google_maps',
            'q'       => "{$query} en {$location}",
            'api_key' => $this->apiKey,
            'num'     => $limit,
        ]);

        return collect($response->json('local_results', []))
            ->map(fn ($r) => new ScrapedBusiness(
                business_name:   $r['title'],
                address:         $r['address'] ?? null,
                phone:           $r['phone'] ?? null,
                website:         $r['website'] ?? null,
                category:        $r['type'] ?? null,
                rating:          $r['rating'] ?? null,
                reviews_count:   $r['reviews'] ?? null,
                google_place_id: $r['place_id'] ?? null,
                latitude:        $r['gps_coordinates']['latitude'] ?? null,
                longitude:       $r['gps_coordinates']['longitude'] ?? null,
                raw_data:        $r,
            ))
            ->all();
    }
}