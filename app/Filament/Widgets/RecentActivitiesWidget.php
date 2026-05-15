<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivitiesWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'Actividad reciente';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->where('user_id', auth()->id())
                    ->with('lead')
                    ->orderByDesc('occurred_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'call'     => 'info',
                        'email'    => 'primary',
                        'meeting'  => 'warning',
                        'whatsapp' => 'success',
                        'note'     => 'gray',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'call'     => 'Llamada',
                        'email'    => 'Email',
                        'meeting'  => 'Reunión',
                        'whatsapp' => 'WhatsApp',
                        'note'     => 'Nota',
                        default    => $state,
                    }),

                TextColumn::make('lead.business_name')
                    ->label('Lead')
                    ->weight('bold')
                    ->url(fn ($record) => $record->lead
                        ? route('filament.admin.resources.leads.edit', $record->lead)
                        : null
                    ),

                TextColumn::make('content')
                    ->label('Descripción')
                    ->limit(60)
                    ->placeholder('—'),
            ])
            ->paginated(false);
    }
}
