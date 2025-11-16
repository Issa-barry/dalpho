<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // ---- Statuts possibles ----
    public const STATUT_ATTENTE = 'attente';
    public const STATUT_ACTIVE  = 'active';
    public const STATUT_BLOQUE  = 'bloque';
    public const STATUT_ARCHIVE = 'archive';

    public const STATUTS = [
        self::STATUT_ATTENTE,
        self::STATUT_ACTIVE,
        self::STATUT_BLOQUE,
        self::STATUT_ARCHIVE,
    ];

    // ---- Rôles possibles ----
    public const ROLE_CLIENT  = 'client';
    public const ROLE_AGENT   = 'agent';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN   = 'admin';

    public const ROLES = [
        self::ROLE_CLIENT,
        self::ROLE_AGENT,
        self::ROLE_MANAGER,
        self::ROLE_ADMIN,
    ];

    protected $fillable = [
        'prenom',
        'nom',
        'email',
        'phone',
        'type_id',
        'numero_id',
        'statut',
        'role',
        'pays',
        'adresse',
        'ville',
        'code_postal',
        'quartier',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
