<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * Les attributs qu'on peut remplir en masse.
     * Protection contre l'assignation de masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Les attributs à cacher lors de la sérialisation en JSON.
     * Ces champs ne seront JAMAIS exposés dans les réponses API.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Définit comment Laravel doit convertir certains champs.
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

    /**
     * Retourne l'identifiant qui sera stocké dans le JWT.
     * Méthode requise par l'interface JWTSubject.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Retourne les claims personnalisés à ajouter au JWT.
     * Méthode requise par l'interface JWTSubject.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
