<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Postgres no acepta insertar/comparar boolean con integer (0/1). Igual que
     * is_active, is_default, etc., forzamos el literal booleano con DB::raw al escribir.
     * Esto evita el error al crear usuarios desde el panel.
     */
    protected function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => match (true) {
                is_bool($value) => $value,
                $value === 't'  => true,
                $value === 'f'  => false,
                default         => (bool) $value,
            },
            set: fn ($value) => ['is_admin' => DB::raw($value ? 'true' : 'false')],
        );
    }
}
