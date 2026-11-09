<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }

    public function viagens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Viagem::class, 'usuario_id');
    }

    public function favoritos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Favorito::class, 'usuario_id');
    }

}
