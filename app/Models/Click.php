<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Click extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'link_id', 'visitor_hash', 'ip_address',
        'country_code', 'country_name', 'city', 'region',
        'latitude', 'longitude',
        'device_type', 'browser', 'browser_version', 'os', 'os_version',
        'user_agent', 'referrer_host', 'referrer_url', 'language',
        'is_unique', 'is_bot', 'clicked_at',
    ];

    protected $casts = [
        'device_type' => DeviceType::class,
        'is_unique'   => 'boolean',
        'is_bot'      => 'boolean',
        'clicked_at'  => 'datetime',
        'latitude'    => 'decimal:7',
        'longitude'   => 'decimal:7',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
