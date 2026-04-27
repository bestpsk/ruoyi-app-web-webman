<?php

return [
    '' => [
        app\middleware\CorsMiddleware::class,
        app\middleware\AuthMiddleware::class,
        app\middleware\LogMiddleware::class,
    ],
];
