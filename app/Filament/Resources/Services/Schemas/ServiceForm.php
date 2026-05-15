<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                TextInput::make('base_price')
                    ->label('Precio base')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('€')
                    ->minValue(0),

                TextInput::make('payment_link')
                    ->label('Link de pago (Stripe)')
                    ->url()
                    ->placeholder('https://buy.stripe.com/...')
                    ->prefix('🔗')
                    ->columnSpanFull()
                    ->suffixAction(
                        \Filament\Actions\Action::make('open_payment_link')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn ($state) => $state ?: null)
                            ->openUrlInNewTab()
                            ->visible(fn ($state) => filled($state))
                    ),

                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),

                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }
}
