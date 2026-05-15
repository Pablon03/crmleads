<?php

namespace App\Services\LeadScraping\Drivers;

use App\Services\LeadScraping\LeadScraperInterface;
use App\Services\LeadScraping\ScrapedBusiness;

/**
 * Driver de prueba que devuelve datos fake realistas en Madrid.
 * Permite que la importación funcione end-to-end sin necesidad de API real.
 *
 * Para añadir un driver real (SerpApi, Outscraper, Apify, Google Places):
 *   1. Crea una clase en este directorio que implemente LeadScraperInterface
 *   2. Añade el driver en config/lead_scraping.php
 *   3. Registra el binding en LeadScrapingServiceProvider
 */
class DummyDriver implements LeadScraperInterface
{
    private array $sampleBusinesses = [
        [
            'business_name' => 'Peluquería Styles Madrid Centro',
            'address'       => 'Calle Gran Vía 45, 28013 Madrid',
            'phone'         => '+34 91 123 45 67',
            'email'         => 'info@stylesmadrid.es',
            'website'       => 'https://stylesmadrid.es',
            'category'      => 'Peluquería',
            'rating'        => 4.5,
            'reviews_count' => 128,
            'latitude'      => 40.4200,
            'longitude'     => -3.7050,
        ],
        [
            'business_name' => 'Fontanería García e Hijos',
            'address'       => 'Calle Alcalá 110, 28009 Madrid',
            'phone'         => '+34 91 234 56 78',
            'email'         => 'fontaneria.garcia@gmail.com',
            'website'       => null,
            'category'      => 'Fontanería',
            'rating'        => 4.2,
            'reviews_count' => 43,
            'latitude'      => 40.4235,
            'longitude'     => -3.6880,
        ],
        [
            'business_name' => 'Restaurante El Rincón Madrileño',
            'address'       => 'Plaza Mayor 3, 28012 Madrid',
            'phone'         => '+34 91 345 67 89',
            'email'         => 'reservas@elrinconmadrileno.es',
            'website'       => 'https://elrinconmadrileno.es',
            'category'      => 'Restaurante',
            'rating'        => 4.8,
            'reviews_count' => 312,
            'latitude'      => 40.4153,
            'longitude'     => -3.7074,
        ],
        [
            'business_name' => 'Clínica Dental Sonrisa Perfecta',
            'address'       => 'Paseo de la Castellana 200, 28046 Madrid',
            'phone'         => '+34 91 456 78 90',
            'email'         => 'citas@sonrisaperfecta.es',
            'website'       => 'https://sonrisaperfecta.es',
            'category'      => 'Clínica dental',
            'rating'        => 4.6,
            'reviews_count' => 87,
            'latitude'      => 40.4503,
            'longitude'     => -3.6917,
        ],
        [
            'business_name' => 'Gimnasio FitZone Madrid',
            'address'       => 'Calle Serrano 55, 28006 Madrid',
            'phone'         => '+34 91 567 89 01',
            'email'         => 'info@fitzomadrid.com',
            'website'       => 'https://fitzonemadrid.com',
            'category'      => 'Gimnasio',
            'rating'        => 4.1,
            'reviews_count' => 201,
            'latitude'      => 40.4280,
            'longitude'     => -3.6850,
        ],
    ];

    public function fetchBusinesses(
        string $query,
        string $location,
        int $radiusMeters = 5000,
        int $limit = 20,
        int $pages = 1,
    ): array {
        $count = min($limit, count($this->sampleBusinesses));

        return array_map(
            fn (array $data) => new ScrapedBusiness(
                business_name: $data['business_name'] . " ({$query} en {$location})",
                address:        $data['address'],
                phone:          $data['phone'],
                email:          $data['email'],
                website:        $data['website'],
                category:       $data['category'],
                rating:         $data['rating'],
                reviews_count:  $data['reviews_count'],
                google_place_id: 'dummy_' . md5($data['business_name'] . $location),
                latitude:       $data['latitude'],
                longitude:      $data['longitude'],
                opening_hours:  ['lunes-viernes' => '09:00-20:00', 'sábado' => '10:00-14:00'],
                images:         [],
                raw_data:       array_merge($data, ['driver' => 'dummy', 'query' => $query, 'location' => $location]),
            ),
            array_slice($this->sampleBusinesses, 0, $count)
        );
    }
}
