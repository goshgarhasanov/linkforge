<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

final class Notification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'uuid', 'user_id', 'type', 'title', 'body',
        'action_url', 'metadata', 'read_at', 'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::creating(static function (Notification $n): void {
            $n->uuid       ??= Uuid::uuid4()->toString();
            $n->created_at ??= now();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
}
