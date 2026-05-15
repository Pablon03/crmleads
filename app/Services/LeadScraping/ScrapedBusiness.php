<?php

namespace App\Services\LeadScraping;

/**
 * DTO que representa un negocio devuelto por el scraper.
 * Todas las propiedades son públicas para facilitar la construcción directa.
 */
class ScrapedBusiness
{
    public function __construct(
        public readonly string $business_name,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $website = null,
        public readonly ?string $category = null,
        public readonly ?float $rating = null,
        public readonly ?int $reviews_count = null,
        public readonly ?string $google_place_id = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?array $opening_hours = null,
        public readonly ?array $images = null,
        public readonly ?array $raw_data = null,
    ) {}
}
