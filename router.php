<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!is_string($path) || $path === '') {
    $path = '/';
}

if (preg_match('#^/(includes|database|archives|game|\.cursor|\.tools)(/|$)#', $path) === 1 || $path === '/router.php') {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}

$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}

if ($path === '/' || $path === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}

$routes = [
    '#^/kunstwerken/?$#' => __DIR__ . '/kunstwerken.php',
    '#^/afrekenen/?$#' => __DIR__ . '/afrekenen.php',
    '#^/over/?$#' => __DIR__ . '/over.php',
    '#^/opdrachten/?$#' => __DIR__ . '/opdrachten.php',
    '#^/contact/?$#' => __DIR__ . '/contact.php',
];

foreach ($routes as $pattern => $script) {
    if (preg_match($pattern, $path) === 1) {
        require $script;
        return true;
    }
}

if (preg_match('#^/kunstwerken/([a-z0-9-]+)/?$#', $path, $matches) === 1) {
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/kunstwerk.php';
    return true;
}

require __DIR__ . '/404.php';
return true;
