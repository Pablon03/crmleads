<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingFollowUpsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Próximos seguimientos';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Lead::query()
                    ->whereNotNull('follow_up_at')
                    ->where('follow_up_at', '>=', now())
                    ->orderBy('follow_up_at', 'asc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('follow_up_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->follow_up_at->isToday() ? 'warning' : 'default'),

                TextColumn::make('business_name')
                    ->label('Negocio')
                    ->searchable()
                    ->weight('bold')
                    ->url(fn ($record) => route('filament.admin.resources.leads.edit', $record)),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('status.name')
                    ->label('Estado')
                    ->badge(),
            ])
            ->paginated(false);
    }
}
