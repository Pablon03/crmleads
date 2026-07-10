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
                    ->label('Cuota')
                    ->money('EUR')
                    ->suffix(fn ($record) => $record->is_recurring ? ' /mes' : '')
                    ->sortable(),

                IconColumn::make('is_recurring')
                    ->label('Recurrente')
                    ->boolean(),

                // Clientes activos con este plan
                TextColumn::make('active_clients_count')
                    ->label('Clientes activos')
                    ->getStateUsing(fn ($record) => $record->leadServices()->where('status', 'sold')->count())
                    ->suffix(' cliente/s')
                    ->badge()
                    ->color('success'),

                // MRR aportado por este plan
                TextColumn::make('plan_mrr')
                    ->label('MRR')
                    ->getStateUsing(fn ($record) => '€' . number_format(
                        $record->leadServices()->where('status', 'sold')
                            ->get()
                            ->sum(fn ($ls) => (float) ($ls->monthly_price ?? $record->base_price)),
                        0, ',', '.'
                    ))
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
