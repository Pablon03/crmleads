<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('base_price')
                    ->label('Precio base')
                    ->money('EUR')
                    ->sortable(),

                // Leads que han comprado este servicio
                TextColumn::make('sold_leads_count')
                    ->label('Vendido a')
                    ->getStateUsing(fn ($record) => $record->leadServices()->where('status', 'sold')->count())
                    ->suffix(' lead/s')
                    ->badge()
                    ->color('success'),

                TextColumn::make('payment_link')
                    ->label('Link de pago')
                    ->placeholder('Sin link')
                    ->limit(30)
                    ->copyable()
                    ->copyableState(fn ($record) => $record->payment_link)
                    ->copyMessage('Link copiado')
                    ->icon('heroicon-o-credit-card')
                    ->url(fn ($record) => $record->payment_link ?: null, shouldOpenInNewTab: true)
                    ->color('primary'),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
