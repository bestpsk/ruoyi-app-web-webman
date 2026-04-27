<?php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'      => 'mysql',
            'host'        => getenv('DB_HOST') ?: '127.0.0.1',
            'port'        => getenv('DB_PORT') ?: '3306',
            'database'    => getenv('DB_DATABASE') ?: 'fuchenpro',
            'username'    => getenv('DB_USERNAME') ?: 'fuchenpro',
            'password'    => getenv('DB_PASSWORD') ?: '123456',
            'charset'     => getenv('DB_CHARSET') ?: 'utf8mb4',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      => getenv('DB_PREFIX') ?: '',
            'strict'      => false,
            'engine'      => null,
            'options'     => [
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
            'pool' => [
                'max_connections' => 20,
                'min_connections' => 5,
                'wait_timeout' => 3,
                'idle_timeout' => 60,
                'heartbeat_interval' => 50,
            ],
        ],
    ],
];
