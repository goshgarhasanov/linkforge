<?php

declare(strict_types=1);

return [
    'api' => [
        'per_minute' => (int) ($_ENV['RATELIMIT_API_PER_MINUTE'] ?? 60),
    ],
    'auth' => [
        'per_minute' => (int) ($_ENV['RATELIMIT_AUTH_PER_MINUTE'] ?? 5),
    ],
    'redirect' => [
        'per_minute' => (int) ($_ENV['RATELIMIT_REDIRECT_PER_MINUTE'] ?? 300),
    ],
];
