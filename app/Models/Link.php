<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

/**
 * @property int       $id
 * @property string    $uuid
 * @property ?int      $user_id
 * @property string    $short_code
 * @property string    $original_url
 * @property ?string   $title
 * @property ?string   $password_hash
 * @property ?\DateTimeInterface $expires_at
 * @property ?int      $max_clicks
 * @property int       $click_count
 * @property int       $unique_click_count
 * @property bool      $is_active
 * @property bool      $is_flagged
 */
final class Link extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'user_id', 'short_code', 'original_url', 'title',
        'password_hash', 'expires_at', 'max_clicks',
        'click_count', 'unique_click_count',
        'utm_parameters', 'ios_deep_link', 'android_deep_link',
        'is_active', 'is_flagged', 'flag_reason',
    ];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'expires_at'         => 'datetime',
        'utm_parameters'     => 'array',
        'is_active'          => 'boolean',
        'is_flagged'         => 'boolean',
        'max_clicks'         => 'integer',
        'click_count'        => 'integer',
        'unique_click_count' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::creating(static function (Link $link): void {
            $link->uuid ??= Uuid::uuid4()->toString();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasReachedClickLimit(): bool
    {
        return $this->max_clicks !== null && $this->click_count >= $this->max_clicks;
    }

    public function requiresPassword(): bool
    {
        return $this->password_hash !== null;
    }

    public function isAccessible(): bool
    {
        return $this->is_active
            && ! $this->is_flagged
            && ! $this->isExpired()
            && ! $this->hasReachedClickLimit();
    }

    public function verifyPassword(string $plain): bool
    {
        return $this->password_hash !== null && password_verify($plain, $this->password_hash);
    }
}
