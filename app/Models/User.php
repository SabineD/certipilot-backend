<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_ZAAKVOERDER = 'zaakvoerder';
    public const ROLE_WERFLEIDER = 'werfleider';
    public const ROLE_PREVENTIEADVISEUR = 'preventieadviseur';

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role',
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
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_ZAAKVOERDER], true);
    }

    public function isZaakvoerder(): bool
    {
        return $this->role === self::ROLE_ZAAKVOERDER;
    }

    public function isWerfleider(): bool
    {
        return $this->role === self::ROLE_WERFLEIDER;
    }

    public function isPreventieadviseur(): bool
    {
        return $this->role === self::ROLE_PREVENTIEADVISEUR;
    }
}
