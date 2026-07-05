<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'usuario';

    protected static ?string $pluralModelLabel = 'usuarios';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }

    // Solo los administradores pueden ver y gestionar usuarios.
    // Cada usuario tiene su propio CRM aislado (leads, estados, etc.),
    // así que exponer esta sección a todos sería una fuga de datos entre cuentas.
    public static function canViewAny(): bool
    {
        return (bool) Auth::user()?->is_admin;
    }

    public static function canCreate(): bool
    {
        return (bool) Auth::user()?->is_admin;
    }

    public static function canEdit($record): bool
    {
        return (bool) Auth::user()?->is_admin;
    }

    public static function canDelete($record): bool
    {
        return (bool) Auth::user()?->is_admin && $record->id !== Auth::id();
    }
}
