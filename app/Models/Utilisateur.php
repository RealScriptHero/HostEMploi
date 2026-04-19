<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Utilisateur extends Authenticatable
{
    use HasFactory;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'motDePasse',
        'role',
        'dateCreation',
    ];

    protected $hidden = [
        'motDePasse',
    ];

    protected $casts = [
        'dateCreation' => 'datetime',
    ];

    /**
     * Get the password for the user.
     *
     * This tells Laravel which column contains the hashed password.
     */
    public function getAuthPassword(): string
    {
        return $this->motDePasse;
    }

    /**
     * Get the password attribute (map to motDePasse for Laravel's auth system).
     */
    public function getPasswordAttribute(): ?string
    {
        return $this->motDePasse ?? null;
    }

    /**
     * Get the name of the password column.
     */
    public function getPasswordName(): string
    {
        return 'motDePasse';
    }

    /**
     * Set the password attribute (automatically hash passwords).
     */
    public function setMotDePasseAttribute($value): void
    {
        if (is_null($value)) {
            $this->attributes['motDePasse'] = null;

            return;
        }

        // Avoid double-hashing when an already-hashed password is provided.
        $this->attributes['motDePasse'] = Hash::needsRehash($value)
            ? Hash::make($value)
            : $value;
    }

    /**
     * Set the password attribute (map from password to motDePasse).
     */
    public function setPasswordAttribute($value): void
    {
        $this->setMotDePasseAttribute($value);
    }
}
