<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Webhook extends Model
{
    protected $fillable = [
        'user_id', 'url', 'secret', 'events', 'is_active',
        'failure_count', 'last_delivered_at', 'last_response',
    ];

    protected $hidden = ['secret'];

    protected $casts = [
        'events'            => 'array',
        'is_active'         => 'boolean',
        'last_delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function listensTo(string $event): bool
    {
        return in_array($event, $this->events ?? [], true) || in_array('*', $this->events ?? [], true);
    }
}
