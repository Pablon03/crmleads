<?php

namespace App\Filament\Resources\LeadStatuses\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeadStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                ColorPicker::make('color')
                    ->label('Color')
                    ->required()
                    ->default('#6b7280'),

                TextInput::make('position')
                    ->label('Posición')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                Toggle::make('is_default')
                    ->label('Estado por defecto')
                    ->helperText('Los nuevos leads importados usarán este estado'),
            ]);
    }
}
