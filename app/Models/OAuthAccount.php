<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OAuthAccount extends Model
{
    protected $table = 'oauth_accounts';

    protected $fillable = [
        'user_id', 'provider', 'provider_user_id', 'provider_email',
        'access_token', 'refresh_token', 'expires_at', 'raw_profile',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = [
        'raw_profile' => 'array',
        'expires_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
