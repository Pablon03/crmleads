<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use App\Services\LeadScraping\LeadScraperInterface;
use App\Services\LeadScraping\ScrapedBusiness;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportLeadsFromGoogleMapsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $userId,
        public readonly ?int $folderId,
        public readonly string $query,
        public readonly string $location,
        public readonly int $radius,
        public readonly int $limit,
        public readonly int $pages = 1,
    ) {}

    public function handle(LeadScraperInterface $scraper): void
    {
        // Estado por defecto del pipeline COMPARTIDO del equipo.
        $defaultStatus = LeadStatus::withoutGlobalScopes()
            ->whereRaw('"is_default" = true')
            ->orderBy('position')
            ->first();

        $businesses = $scraper->fetchBusinesses(
            query:         $this->query,
            location:      $this->location,
            radiusMeters:  $this->radius,
            limit:         $this->limit,
            pages:         $this->pages,
        );

        $newCount     = 0;
        $updatedCount = 0;

        foreach ($businesses as $business) {
            $data = $this->mapToLeadData($business, $defaultStatus?->id);

            // Upsert por user_id + google_place_id para evitar duplicados
            if ($business->google_place_id) {
                $existing = Lead::withoutGlobalScopes()
                    ->where('user_id', $this->userId)
                    ->where('google_place_id', $business->google_place_id)
                    ->first();

                if ($existing) {
                    $existing->update($data);
                    $updatedCount++;
                } else {
                    Lead::create(array_merge($data, ['user_id' => $this->userId]));
                    $newCount++;
                }
            } else {
                Lead::create(array_merge($data, ['user_id' => $this->userId]));
                $newCount++;
            }
        }

        // Notificación al usuario al terminar
        $user = User::find($this->userId);
        if ($user) {
            Notification::make()
                ->title('Importación completada')
                ->body("Se importaron {$newCount} leads nuevos y se actualizaron {$updatedCount}.")
                ->success()
                ->sendToDatabase($user);
        }
    }

    private function mapToLeadData(ScrapedBusiness $business, ?int $statusId): array
    {
        return [
            'folder_id'      => $this->folderId,
            'status_id'      => $statusId,
            'business_name'  => $business->business_name,
            'address'        => $business->address,
            'phone'          => $business->phone,
            'email'          => $business->email,
            'website'        => $business->website,
            'category'       => $business->category,
            'rating'         => $business->rating,
            'reviews_count'  => $business->reviews_count,
            'google_place_id'=> $business->google_place_id,
            'latitude'       => $business->latitude,
            'longitude'      => $business->longitude,
            'opening_hours'  => $business->opening_hours,
            'images'         => $business->images,
            'raw_data'       => $business->raw_data,
        ];
    }
}
