<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Lead;
use App\Models\Service;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'income'  => 'Ingreso',
                        'expense' => 'Gasto',
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->label('Importe')
                    ->required()
                    ->numeric()
                    ->prefix('€')
                    ->minValue(0),

                TextInput::make('description')
                    ->label('Descripción')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                DatePicker::make('transacted_at')
                    ->label('Fecha')
                    ->required()
                    ->default(now()),

                Select::make('lead_id')
                    ->label('Lead asociado')
                    ->options(fn () => Lead::orderBy('business_name')->pluck('business_name', 'id'))
                    ->searchable()
                    ->nullable(),

                Select::make('service_id')
                    ->label('Servicio asociado')
                    ->options(fn () => Service::pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
            ]);
    }
}
