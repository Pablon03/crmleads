<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Models\Folder;
use App\Models\LeadStatus;
use App\Models\Service;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([

                        // ── Tab 1: Información ──────────────────────────────
                        Tab::make('Información')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                TextInput::make('business_name')
                                    ->label('Nombre del negocio')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                TextInput::make('category')
                                    ->label('Categoría')
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->maxLength(50),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('website')
                                    ->label('Sitio web')
                                    ->url()
                                    ->maxLength(255),

                                Textarea::make('address')
                                    ->label('Dirección')
                                    ->columnSpanFull()
                                    ->rows(2),

                                TextInput::make('rating')
                                    ->label('Rating')
                                    ->numeric()
                                    ->step(0.1)
                                    ->minValue(0)
                                    ->maxValue(5),

                                TextInput::make('reviews_count')
                                    ->label('Nº de reseñas')
                                    ->numeric()
                                    ->minValue(0),

                                TextInput::make('latitude')
                                    ->label('Latitud')
                                    ->numeric(),

                                TextInput::make('longitude')
                                    ->label('Longitud')
                                    ->numeric(),

                                Select::make('folder_id')
                                    ->label('Carpeta')
                                    ->options(fn () => Folder::pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),

                                Select::make('status_id')
                                    ->label('Estado')
                                    ->options(fn () => LeadStatus::orderBy('position')->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),

                                DateTimePicker::make('follow_up_at')
                                    ->label('Seguimiento programado')
                                    ->nullable(),

                                Textarea::make('notes')
                                    ->label('Notas')
                                    ->columnSpanFull()
                                    ->rows(4),
                            ])
                            ->columns(2),

                        // ── Tab 2: Servicios ────────────────────────────────
                        Tab::make('Servicios')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Repeater::make('leadServices')
                                    ->label('Servicios asociados')
                                    ->relationship()
                                    ->schema([
                                        Select::make('service_id')
                                            ->label('Servicio')
                                            ->options(fn () => Service::active()->pluck('name', 'id'))
                                            ->required()
                                            ->live()
                                            ->columnSpan(2),

                                        TextInput::make('payment_link_display')
                                            ->label('Link de pago')
                                            ->placeholder('Sin link de pago configurado')
                                            ->prefix('🔗')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpan(2)
                                            ->afterStateHydrated(function ($set, $get) {
                                                $service = Service::find($get('service_id'));
                                                $set('payment_link_display', $service?->payment_link);
                                            })
                                            ->suffixActions([
                                                \Filament\Actions\Action::make('copy_payment_link')
                                                    ->icon('heroicon-o-clipboard-document')
                                                    ->alpineClickHandler('window.navigator.clipboard.writeText($el.closest(\'[data-field]\')?.querySelector(\'input\')?.value ?? \'\')')
                                                    ->tooltip('Copiar link')
                                                    ->visible(fn ($get) => filled(Service::find($get('service_id'))?->payment_link)),
                                                \Filament\Actions\Action::make('open_payment_link')
                                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                                    ->url(fn ($get) => Service::find($get('service_id'))?->payment_link)
                                                    ->openUrlInNewTab()
                                                    ->tooltip('Abrir en Stripe')
                                                    ->visible(fn ($get) => filled(Service::find($get('service_id'))?->payment_link)),
                                            ]),

                                        Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'interested' => 'Interesado',
                                                'proposed'   => 'Propuesta enviada',
                                                'sold'       => 'Vendido',
                                                'rejected'   => 'Rechazado',
                                            ])
                                            ->required()
                                            ->default('interested'),

                                        TextInput::make('sold_price')
                                            ->label('Precio de venta')
                                            ->numeric()
                                            ->prefix('€')
                                            ->nullable(),

                                        DateTimePicker::make('sold_at')
                                            ->label('Fecha de venta')
                                            ->nullable(),

                                        Textarea::make('notes')
                                            ->label('Notas')
                                            ->columnSpanFull()
                                            ->rows(2),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->addActionLabel('Añadir servicio')
                                    ->collapsible()
                                    ->defaultItems(0),
                            ]),

                        // ── Tab 3: Actividades ──────────────────────────────
                        Tab::make('Actividades')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Repeater::make('activities')
                                    ->label('Historial de interacciones')
                                    ->relationship()
                                    ->schema([
                                        Select::make('type')
                                            ->label('Tipo')
                                            ->options([
                                                'call'      => 'Llamada',
                                                'email'     => 'Email',
                                                'meeting'   => 'Reunión',
                                                'whatsapp'  => 'WhatsApp',
                                                'note'      => 'Nota',
                                            ])
                                            ->required(),

                                        DateTimePicker::make('occurred_at')
                                            ->label('Fecha y hora')
                                            ->required()
                                            ->default(now()),

                                        Textarea::make('content')
                                            ->label('Descripción')
                                            ->required()
                                            ->columnSpanFull()
                                            ->rows(3),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->addActionLabel('Añadir actividad')
                                    ->collapsible()
                                    ->defaultItems(0)
                                    ->orderColumn('occurred_at'),
                            ]),
                    ]),
            ]);
    }
}
