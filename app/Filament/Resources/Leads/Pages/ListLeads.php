<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Jobs\ImportLeadsFromGoogleMapsJob;
use App\Models\Folder;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_google_maps')
                ->label('Importar desde Google Maps')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    TextInput::make('query')
                        ->label('Tipo de negocio')
                        ->placeholder('Ej: peluquerías, restaurantes, fontaneros...')
                        ->required(),

                    Textarea::make('locations')
                        ->label('Ubicaciones (una por línea)')
                        ->placeholder("Sevilla\nDos Hermanas\nAlcalá de Guadaíra\nUtrera\nMorón de la Frontera")
                        ->helperText('Pon cada pueblo o ciudad en una línea. Se lanzará un job por cada ubicación. Los duplicados se detectan automáticamente.')
                        ->rows(5)
                        ->requiredWithout('lat'),

                    Fieldset::make('O usa coordenadas GPS exactas (solo una ubicación)')
                        ->schema([
                            TextInput::make('lat')
                                ->label('Latitud')
                                ->placeholder('Ej: 37.3891')
                                ->numeric()
                                ->nullable(),

                            TextInput::make('lng')
                                ->label('Longitud')
                                ->placeholder('Ej: -5.9845')
                                ->numeric()
                                ->nullable(),
                        ])
                        ->columns(2),

                    TextInput::make('radius_meters')
                        ->label('Radio por ubicación (metros)')
                        ->numeric()
                        ->default(5000)
                        ->minValue(100)
                        ->maxValue(50000)
                        ->helperText('5.000 m para pueblos pequeños, 10.000–20.000 m para capitales.'),

                    TextInput::make('pages')
                        ->label('Páginas por ubicación')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->maxValue(10)
                        ->helperText('Cada página = 20 resultados. 3 páginas = hasta 60 por ubicación.'),

                    Select::make('folder_id')
                        ->label('Guardar en carpeta')
                        ->options(fn () => Folder::pluck('name', 'id'))
                        ->nullable()
                        ->placeholder('Sin carpeta'),
                ])
                ->action(function (array $data) {
                    // Coordenadas GPS → una sola ubicación
                    if (filled($data['lat']) && filled($data['lng'])) {
                        $locations = ["{$data['lat']},{$data['lng']}"];
                    } else {
                        $locations = array_filter(
                            array_map('trim', explode("\n", $data['locations'] ?? '')),
                            fn ($l) => $l !== ''
                        );
                    }

                    $count = count($locations);

                    foreach ($locations as $location) {
                        ImportLeadsFromGoogleMapsJob::dispatch(
                            userId:   auth()->id(),
                            folderId: $data['folder_id'] ?? null,
                            query:    $data['query'],
                            location: $location,
                            radius:   (int) ($data['radius_meters'] ?? 5000),
                            limit:    20,
                            pages:    (int) ($data['pages'] ?? 1),
                        );
                    }

                    $totalEstimado = $count * 20 * (int) ($data['pages'] ?? 1);

                    Notification::make()
                        ->title("Importación iniciada: {$count} " . ($count === 1 ? 'ubicación' : 'ubicaciones'))
                        ->body("Se lanzaron {$count} búsquedas (hasta {$totalEstimado} resultados). Los duplicados se filtran automáticamente.")
                        ->success()
                        ->send();
                }),

            CreateAction::make()
                ->label('Nuevo lead'),
        ];
    }
}
