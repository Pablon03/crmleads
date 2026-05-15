<?php

namespace App\Filament\Resources\LeadStatuses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadStatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                ColorColumn::make('color')
                    ->label('Color'),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('leads_count')
                    ->label('Leads')
                    ->counts('leads')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_default')
                    ->label('Por defecto')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->filters([])
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
