<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Leads\Schemas\LeadForm;
use App\Models\Lead;
use App\Models\LeadStatus;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardPage;
use Relaticle\Flowforge\Column;

class LeadKanbanPage extends BoardPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static ?string $navigationLabel = 'Kanban';

    protected static ?string $title = 'Kanban de Leads';

    protected static ?int $navigationSort = 1;

    public function board(Board $board): Board
    {
        $statuses = LeadStatus::orderBy('position')->get();

        $columns = $statuses->map(function (LeadStatus $status) {
            return Column::make((string) $status->id)
                ->label($status->name)
                ->color($status->color);
        })->all();

        return $board
            ->columns($columns)
            ->query(Lead::query()->with(['folder', 'status', 'assignedUser']))
            ->columnIdentifier('status_id')
            ->positionIdentifier('kanban_order')
            ->recordTitleAttribute('business_name')
            ->cardAction('edit_lead')
            ->cardSchema(function ($schema) {
                return $schema->components([
                    TextEntry::make('priority_score')
                        ->hiddenLabel()
                        ->badge()
                        ->formatStateUsing(fn ($state) => is_null($state) ? null : 'Prioridad ' . (int) $state)
                        ->color(fn ($state) => match (true) {
                            $state >= 70 => 'success',
                            $state >= 45 => 'warning',
                            default      => 'gray',
                        }),
                    TextEntry::make('category')
                        ->hiddenLabel()
                        ->badge()
                        ->color('gray'),
                    TextEntry::make('assignedUser.name')
                        ->hiddenLabel()
                        ->icon('heroicon-o-user')
                        ->placeholder('Sin asignar')
                        ->size('sm'),
                    TextEntry::make('phone')
                        ->hiddenLabel()
                        ->icon('heroicon-o-phone')
                        ->placeholder('Sin teléfono')
                        ->size('sm'),
                    TextEntry::make('address')
                        ->hiddenLabel()
                        ->icon('heroicon-o-map-pin')
                        ->placeholder('Sin dirección')
                        ->size('sm')
                        ->limit(40),
                    TextEntry::make('rating')
                        ->hiddenLabel()
                        ->icon('heroicon-o-star')
                        ->placeholder('—')
                        ->size('sm')
                        ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) . ' ★' : '—'),
                ]);
            })
            ->headerToolbar();
    }

    protected function getActions(): array
    {
        return [
            Action::make('edit_lead')
                ->label('Editar lead')
                ->slideOver()
                ->fillForm(fn ($record) => $record->load(['leadServices', 'activities'])->toArray())
                ->schema(fn (Schema $schema) => LeadForm::configure($schema))
                ->action(function ($record, array $data) {
                    $record->update(collect($data)->except(['leadServices', 'activities'])->toArray());

                    Notification::make()
                        ->title('Lead actualizado')
                        ->success()
                        ->send();
                })
                ->modalWidth('5xl'),
        ];
    }
}
