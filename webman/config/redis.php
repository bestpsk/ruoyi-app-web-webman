<?php

return [
    'client' => 'predis',
    'default' => [
        'password' => getenv('REDIS_PASSWORD') ?: '',
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        'port' => getenv('REDIS_PORT') ?: 6379,
        'database' => getenv('REDIS_DB') ?: 0,
        'pool' => [
            'max_connections' => 10,
            'min_connections' => 2,
            'wait_timeout' => 3,
            'idle_timeout' => 60,
            'heartbeat_interval' => 50,
        ],
    ]
];
