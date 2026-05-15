<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionPlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'plan', 'status',
        'stripe_customer_id', 'stripe_subscription_id', 'stripe_price_id',
        'current_period_start', 'current_period_end',
        'trial_ends_at', 'canceled_at',
    ];

    protected $casts = [
        'plan'                 => SubscriptionPlan::class,
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'trial_ends_at'        => 'datetime',
        'canceled_at'          => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trialing', 'active'], true);
    }

    public function onPaidPlan(): bool
    {
        return $this->isActive() && $this->plan !== SubscriptionPlan::Free;
    }
}
