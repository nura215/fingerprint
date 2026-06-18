<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'Admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string|array $roles): bool
    {
        $roleName = $this->role?->name;

        if (! $roleName) {
            return false;
        }

        $normalize = fn (string $role): string => str($role)->lower()->replace([' ', '-'], '_')->toString();

        return in_array($normalize($roleName), array_map($normalize, (array) $roles), true);
    }

    public function roleLabel(): string
    {
        return $this->role?->name ?? 'user';
    }
}



