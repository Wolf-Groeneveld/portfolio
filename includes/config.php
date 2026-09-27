<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

const SITE_NAME = 'Toos Boerlage';
const SITE_TITLE = 'Toos Boerlage | Kunst & Originele Schilderijen';
const SITE_DESCRIPTION = 'Ontdek originele kunstwerken van kunstenares Toos Boerlage. Bekijk de collectie schilderijen en unieke werken online.';

const ARTIST_EMAIL = 'info@allesvoorkunst.nl';
const ARTIST_PHONE_DISPLAY = '0181-323808';
const ARTIST_PHONE_TEL = '+31181323808';
const ARTIST_STREET = 'Rijksstraatweg 30';
const ARTIST_POSTCODE = '3221 KA';
const ARTIST_CITY = 'Hellevoetsluis';

const ARTIST_WEBSITES = [
    ['label' => 'Alles voor Kunst', 'url' => 'https://www.allesvoorkunst.nl'],
    ['label' => 'Kunstuitleen Hellevoetsluis', 'url' => 'https://www.kunstuitleenhellevoetsluis.nl'],
];

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'portfolio');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

function asset(string $path): string
{
    $file = dirname(__DIR__) . '/' . ltrim($path, '/');
    $version = is_file($file) ? filemtime($file) : 1;

    return '/' . ltrim($path, '/') . '?v=' . $version;
}
