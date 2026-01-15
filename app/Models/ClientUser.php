<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ClientUser extends Authenticatable
{
    use SoftDeletes, Notifiable;

    protected $fillable = [
        'client_id',
        'name',
        'email',
        'password',
        'phone',
        'role',
        'permissions',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Verificar si tiene un permiso específico.
     */
    public function hasPermission(string $permission): bool
    {
        // Admin tiene todos los permisos
        if ($this->role === 'admin') {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Verificar si es admin del cliente.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Actualizar último login.
     */
    public function recordLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}
