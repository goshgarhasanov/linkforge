<?php

declare(strict_types=1);

return [
    'secret'      => $_ENV['JWT_SECRET']      ?? '',
    'algorithm'   => $_ENV['JWT_ALGORITHM']   ?? 'HS256',
    'ttl'         => (int) ($_ENV['JWT_TTL'] ?? 3600),
    'refresh_ttl' => (int) ($_ENV['JWT_REFRESH_TTL'] ?? 1209600),
    'issuer'      => $_ENV['APP_URL']         ?? 'linkforge',
    'audience'    => 'linkforge-clients',
];
