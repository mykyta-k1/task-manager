<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$uri = $_SERVER['REQUEST_URI'];

$response = match ($uri) {
    '/' => fn() => require __DIR__ . '/views/home.php',
    '/login' => fn() => require __DIR__ . '/views/login.php',
    '/register' => fn() => require __DIR__ . '/views/register.php',
    '/tasks' => fn() => require __DIR__ . '/views/tasks.php',
    default => fn() => require __DIR__ . '/views/404.php'
};

$response();