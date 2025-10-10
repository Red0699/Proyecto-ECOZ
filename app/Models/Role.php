<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * Un rol puede tener muchos usuarios.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return match ($this->name) {
            'admin' => 'Administrador',
            'user-cota' => 'Usuario Cota',
            'user-ubate' => 'Usuario Ubaté',
            'user-silvania' => 'Usuario Silvania',
            default => $this->name, 
        };
    }
}
