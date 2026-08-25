<?php

ini_set('display_errors', '0');
ini_set('log_errors', '1');

function asset(string $path): string
{
    $file = dirname(__DIR__) . '/' . ltrim($path, '/');
    $v = is_file($file) ? filemtime($file) : 1;
    return $path . '?v=' . $v;
}

// ---- Persoonlijke info ----
define('SITE_NAME',      'Wolf Groeneveld');
define('SITE_TITLE',     'Wolf Groeneveld — Portfoliosite');
define('SITE_TAGLINE',   'Derdejaars student aan het Grafisch Lyceum.');
define('SITE_LOCATION',  'Hellevoetsluis, Nederland');
define('SITE_EMAIL',     'wolfgroeneveld@gmail.com');
define('GITHUB_URL',     'https://github.com/Wolf-Groeneveld/portfolio');
define('LINKEDIN_URL',   ''); // leeg = wordt niet getoond
define('RESUME_PATH',    'assets/img/cv.png');

// ---- Over mij ----
define('ABOUT_TEXT', <<<'TXT'
Mijn naam is Wolf Groeneveld en ik ben 18 jaar oud. Ik bouw websites — van de database tot de voorkant.

Ik combineer technische vaardigheden met aandacht voor design en gebruikerservaring. Ik wil sites maken die helder zijn, goed werken en waar een klant écht iets aan heeft.

Op dit moment verdiep ik me in PHP en MySQL. Later wil ik als zzp'er websites maken voor klanten.
TXT);

// ---- Database ----
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'portfolio');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
