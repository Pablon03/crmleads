<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Models\Folder;
use App\Models\LeadStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\Select;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')
                    ->label('Negocio')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->category),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Teléfono copiado')
                    ->placeholder('—'),

                IconColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->state(fn ($record) => self::whatsappState($record))
                    ->icon(fn (string $state) => match ($state) {
                        'verified_yes' => 'heroicon-s-check-circle',
                        'verified_no'  => 'heroicon-s-x-circle',
                        'mobile'       => 'heroicon-o-chat-bubble-left-ellipsis',
                        'landline'     => 'heroicon-o-phone',
                        default        => 'heroicon-o-x-circle',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'verified_yes' => 'success',
                        'verified_no'  => 'danger',
                        'mobile'       => 'warning',
                        'landline'     => 'warning',
                        default        => 'gray',
                    })
                    ->url(fn ($record) => self::whatsappState($record) === 'verified_yes'
                        ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $record->phone)
                        : null,
                        shouldOpenInNewTab: true
                    )
                    ->tooltip(fn ($record) => match (self::whatsappState($record)) {
                        'verified_yes' => '✓ Verificado con WhatsApp · ' . $record->phone,
                        'verified_no'  => '✗ Verificado sin WhatsApp · ' . $record->phone,
                        'mobile'       => 'Móvil · sin verificar · ' . $record->phone,
                        'landline'     => 'Fijo · probablemente sin WhatsApp · ' . $record->phone,
                        default        => 'Sin teléfono',
                    }),

                TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($record) => $record->status?->color ? 'primary' : 'gray'),

                TextColumn::make('folder.name')
                    ->label('Carpeta')
                    ->badge()
                    ->color('info')
                    ->placeholder('Sin carpeta'),

                IconColumn::make('website')
                    ->label('Web')
                    ->boolean()
                    ->state(fn ($record) => filled($record->website))
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->url(fn ($record) => $record->website ?: null, shouldOpenInNewTab: true)
                    ->tooltip(fn ($record) => $record->website ?: 'Sin página web'),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Buscar por nombre, teléfono o dirección...')
            ->filters([
                SelectFilter::make('folder_id')
                    ->label('Carpeta')
                    ->options(fn () => Folder::pluck('name', 'id'))
                    ->placeholder('Todas las carpetas'),

                SelectFilter::make('status_id')
                    ->label('Estado')
                    ->options(fn () => LeadStatus::orderBy('position')->pluck('name', 'id'))
                    ->placeholder('Todos los estados'),

                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(fn () => \App\Models\Lead::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category'))
                    ->placeholder('Todas las categorías'),

                Filter::make('mobile_phone')
                    ->label('Móvil (WhatsApp)')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('phone')
                        ->whereRaw("regexp_replace(phone, '[^0-9]', '', 'g') ~ '^(34)?[67]'")
                    ),

                Filter::make('landline_phone')
                    ->label('Teléfono fijo (sin WhatsApp)')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('phone')
                        ->whereRaw("regexp_replace(phone, '[^0-9]', '', 'g') ~ '^(34)?[89]'")
                    ),

                Filter::make('without_phone')
                    ->label('Sin teléfono')
                    ->query(fn (Builder $query) => $query->whereNull('phone')->orWhere('phone', '')),

                Filter::make('without_website')
                    ->label('Sin página web')
                    ->query(fn (Builder $query) => $query->whereNull('website')->orWhere('website', '')),

                Filter::make('with_website')
                    ->label('Con página web')
                    ->query(fn (Builder $query) => $query->whereNotNull('website')->where('website', '!=', '')),

                Filter::make('min_rating')
                    ->label('Rating mínimo 4+')
                    ->query(fn (Builder $query) => $query->where('rating', '>=', 4)),

                Filter::make('follow_up_due')
                    ->label('Follow-up pendiente')
                    ->query(fn (Builder $query) => $query->withFollowUpDue()),
            ])
            ->recordActions([
                Action::make('verify_whatsapp')
                    ->label('Verificar WhatsApp')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->size('sm')
                    ->visible(fn ($record) => filled($record->phone))
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => 'Verificar WhatsApp: ' . $record->phone)
                    ->modalDescription('Esto consume 1 de tus 20 consultas diarias. ¿Continuar?')
                    ->modalSubmitActionLabel('Verificar')
                    ->action(function ($record) {
                        $digits = preg_replace('/[^0-9]/', '', $record->phone);

                        // Añadir prefijo 34 si es número español sin código de país
                        if (strlen($digits) === 9) {
                            $digits = '34' . $digits;
                        }

                        $response = Http::withHeaders([
                            'X-Rapidapi-Key'  => config('lead_scraping.api_key'),
                            'X-Rapidapi-Host' => 'whatsapp-number-validator3.p.rapidapi.com',
                            'Content-Type'    => 'application/json',
                        ])->post('https://whatsapp-number-validator3.p.rapidapi.com/WhatsappNumberHasItWithToken', [
                            'phone_number' => $digits,
                        ]);

                        if (! $response->successful()) {
                            Notification::make()
                                ->title('Error al verificar')
                                ->body('No se pudo contactar con la API. Código: ' . $response->status())
                                ->danger()
                                ->send();
                            return;
                        }

                        $hasWhatsapp = $response->json('status') === 'valid';
                        $record->update(['has_whatsapp' => $hasWhatsapp]);

                        Notification::make()
                            ->title($hasWhatsapp ? '✓ Tiene WhatsApp' : '✗ Sin WhatsApp')
                            ->body($record->business_name . ' · ' . $record->phone)
                            ->status($hasWhatsapp ? 'success' : 'warning')
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('move_to_folder')
                        ->label('Mover a carpeta')
                        ->icon('heroicon-o-folder')
                        ->form([
                            Select::make('folder_id')
                                ->label('Carpeta')
                                ->options(fn () => Folder::pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) =>
                            $records->each->update(['folder_id' => $data['folder_id']])
                        ),

                    BulkAction::make('change_status')
                        ->label('Cambiar estado')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('status_id')
                                ->label('Estado')
                                ->options(fn () => LeadStatus::orderBy('position')->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) =>
                            $records->each->update(['status_id' => $data['status_id']])
                        ),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function whatsappState($record): string
    {
        if (! is_null($record->has_whatsapp)) {
            return $record->has_whatsapp ? 'verified_yes' : 'verified_no';
        }

        return self::phoneType($record->phone);
    }

    private static function phoneType(?string $phone): string
    {
        if (blank($phone)) {
            return 'none';
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        // Quitar prefijo de España (34) si viene con él
        if (strlen($digits) === 11 && str_starts_with($digits, '34')) {
            $digits = substr($digits, 2);
        }

        $first = $digits[0] ?? '';

        return match (true) {
            in_array($first, ['6', '7']) => 'mobile',
            in_array($first, ['8', '9']) => 'landline',
            default                      => 'none',
        };
    }
}
