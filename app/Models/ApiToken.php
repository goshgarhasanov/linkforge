<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ApiToken extends Model
{
    protected $fillable = [
        'user_id', 'name', 'token_hash', 'token_prefix',
        'abilities', 'usage_count', 'last_used_at', 'last_used_ip',
        'expires_at', 'revoked_at',
    ];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'abilities'     => 'array',
        'last_used_at'  => 'datetime',
        'expires_at'    => 'datetime',
        'revoked_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return ! $this->isRevoked() && ! $this->isExpired();
    }

    public function hasAbility(string $ability): bool
    {
        return in_array($ability, $this->abilities ?? [], true)
            || in_array('*', $this->abilities ?? [], true);
    }
}
