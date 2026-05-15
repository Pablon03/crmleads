<?php

namespace App\Filament\Resources\Folders\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FolderForm
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
                    ->nullable(),

                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }
}
