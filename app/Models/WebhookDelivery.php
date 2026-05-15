<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WebhookDelivery extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'webhook_id', 'event', 'payload', 'status_code',
        'response_body', 'duration_ms', 'was_successful', 'attempted_at',
    ];

    protected $casts = [
        'payload'        => 'array',
        'was_successful' => 'boolean',
        'attempted_at'   => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
