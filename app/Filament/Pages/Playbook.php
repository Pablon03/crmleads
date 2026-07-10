<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Playbook de captación y seguimiento, accesible para todo el equipo dentro del CRM.
 * Contenido estático (guiones y criterios) renderizado desde una vista Blade.
 */
class Playbook extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'Playbook de ventas';

    protected static ?string $title = 'Playbook de captación y seguimiento';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.playbook';
}
