<?php

declare(strict_types=1);

$uri = $_SERVER['REQUEST_URI'];

$file = __DIR__ . $uri;

if (file_exists($file)) {
    // If file exist, PHP server returning this file
    return false;
}

// If file does not exist
require_once __DIR__ . '/index.php';