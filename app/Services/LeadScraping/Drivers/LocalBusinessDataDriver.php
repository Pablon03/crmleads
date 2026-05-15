<?php

namespace App\Services\LeadScraping\Drivers;

use App\Services\LeadScraping\LeadScraperInterface;
use App\Services\LeadScraping\ScrapedBusiness;
use Illuminate\Support\Facades\Http;

/**
 * Driver para la API "Local Business Data" de RapidAPI.
 * Endpoint: https://local-business-data.p.rapidapi.com/search
 *
 * El parámetro $location debe tener formato "lat,lng" (ej: "40.7128,-74.006").
 * El radio en metros se convierte automáticamente a un nivel de zoom de Google Maps.
 */
class LocalBusinessDataDriver implements LeadScraperInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $host = 'local-business-data.p.rapidapi.com',
        private readonly string $language = 'es',
        private readonly string $region = 'es',
    ) {}

    public function fetchBusinesses(
        string $query,
        string $location,
        int $radiusMeters = 5000,
        int $limit = 20,
        int $pages = 1,
    ): array {
        [$lat, $lng] = $this->parseCoordinates($location);

        $perPage = 20; // máximo que acepta la API por request
        $results  = [];

        for ($page = 0; $page < $pages; $page++) {
            $response = Http::withHeaders([
                'x-rapidapi-host' => $this->host,
                'x-rapidapi-key'  => $this->apiKey,
            ])->get("https://{$this->host}/search", [
                'query'    => $query,
                'limit'    => $perPage,
                'offset'   => $page * $perPage,
                'lat'      => $lat,
                'lng'      => $lng,
                'zoom'     => $this->radiusToZoom($radiusMeters),
                'language' => $this->language,
                'region'   => $this->region,
            ]);

            $response->throw();

            $data = $response->json('data', []);

            foreach ($data as $item) {
                $results[] = $this->mapToBusiness($item);
            }

            // Si la API devolvió menos de perPage, no hay más resultados
            if (count($data) < $perPage) {
                break;
            }
        }

        return $results;
    }

    private function mapToBusiness(array $item): ScrapedBusiness
    {
        // Foto principal (primer elemento de photos_sample si existe)
        $photos = collect($item['photos_sample'] ?? [])
            ->pluck('photo_url')
            ->filter()
            ->values()
            ->all();

        // Horario de apertura
        $hours = null;
        if (! empty($item['opening_hours'])) {
            $hours = collect($item['opening_hours'])->mapWithKeys(function (array $entry) {
                return [$entry['day'] ?? '?' => ($entry['hours'] ?? 'Cerrado')];
            })->all();
        }

        return new ScrapedBusiness(
            business_name:   $item['name'] ?? 'Sin nombre',
            address:         $item['full_address'] ?? null,
            phone:           $item['phone_number'] ?? null,
            email:           null,   // la API no devuelve email
            website:         $item['website'] ?? null,
            category:        $item['type'] ?? null,
            rating:          isset($item['rating']) ? (float) $item['rating'] : null,
            reviews_count:   isset($item['review_count']) ? (int) $item['review_count'] : null,
            google_place_id: $item['place_id'] ?? null,
            latitude:        isset($item['latitude']) ? (float) $item['latitude'] : null,
            longitude:       isset($item['longitude']) ? (float) $item['longitude'] : null,
            opening_hours:   $hours,
            images:          $photos ?: null,
            raw_data:        $item,
        );
    }

    /**
     * Parsea "lat,lng" o geocodifica un nombre de ciudad via Nominatim.
     */
    private function parseCoordinates(string $location): array
    {
        $parts = array_map('trim', explode(',', $location));

        if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
            return [(float) $parts[0], (float) $parts[1]];
        }

        // Geocodificar nombre de ciudad con Nominatim (OpenStreetMap, sin clave)
        $geo = Http::withHeaders(['User-Agent' => 'CRMLeads/1.0'])
            ->get('https://nominatim.openstreetmap.org/search', [
                'q'      => $location,
                'format' => 'json',
                'limit'  => 1,
            ]);

        $results = $geo->json();

        if (empty($results)) {
            throw new \InvalidArgumentException(
                "No se encontraron coordenadas para la ubicación: \"{$location}\""
            );
        }

        return [(float) $results[0]['lat'], (float) $results[0]['lon']];
    }

    /**
     * Convierte un radio en metros a un nivel de zoom de Google Maps (1-21).
     * Aproximación: zoom 13 ≈ 5 km de radio en pantalla estándar.
     */
    private function radiusToZoom(int $radiusMeters): int
    {
        return match (true) {
            $radiusMeters <= 500   => 16,
            $radiusMeters <= 1000  => 15,
            $radiusMeters <= 2000  => 14,
            $radiusMeters <= 5000  => 13,
            $radiusMeters <= 10000 => 12,
            $radiusMeters <= 20000 => 11,
            $radiusMeters <= 50000 => 10,
            default                => 9,
        };
    }
}
