<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

/**
 * @property int        $id
 * @property string     $uuid
 * @property string     $name
 * @property string     $email
 * @property string     $password_hash
 * @property UserRole   $role
 * @property bool       $is_active
 * @property bool       $two_factor_enabled
 * @property ?string    $two_factor_secret
 */
final class User extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'name', 'email', 'password_hash', 'role',
        'avatar_url', 'locale', 'email_verified_at',
        'two_factor_secret', 'two_factor_enabled',
        'last_login_at', 'last_login_ip', 'is_active',
    ];

    protected $hidden = ['password_hash', 'two_factor_secret'];

    protected $casts = [
        'role'               => UserRole::class,
        'is_active'          => 'boolean',
        'two_factor_enabled' => 'boolean',
        'email_verified_at'  => 'datetime',
        'last_login_at'      => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::creating(static function (User $user): void {
            $user->uuid ??= Uuid::uuid4()->toString();
        });
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function verifyPassword(string $plain): bool
    {
        return password_verify($plain, $this->password_hash);
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }
}
