<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function site_url(string $path = ''): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = ltrim($path, '/');

    return ($https ? 'https' : 'http') . '://' . $host . ($path === '' ? '/' : '/' . $path);
}

function format_eur(int|float $amount): string
{
    $formatted = number_format(abs((float) $amount), 2, ',', '.');

    return ($amount < 0 ? '−' : '') . '€ ' . $formatted;
}

function format_bytes(int $bytes): string
{
    if ($bytes >= 1024 * 1024) {
        $mb = $bytes / (1024 * 1024);
        $label = abs($mb - round($mb)) < 0.05 ? (string) (int) round($mb) : number_format($mb, 1, ',', '.');

        return $label . ' MB';
    }

    return (string) max(1, (int) round($bytes / 1024)) . ' KB';
}

function price_label(array $artwork): string
{
    if (($artwork['status'] ?? '') === 'sold') {
        return 'Verkocht';
    }
    if (!isset($artwork['price'])) {
        return 'Prijs op aanvraag';
    }

    return format_eur($artwork['price']);
}

function status_label(string $status): string
{
    return match ($status) {
        'available' => 'Beschikbaar',
        'sold' => 'Verkocht',
        'inquiry' => 'Op aanvraag',
        default => 'Onbekend',
    };
}

function can_purchase(array $artwork): bool
{
    return ($artwork['status'] ?? '') === 'available' && isset($artwork['price']) && is_numeric($artwork['price']);
}

function artwork_meta_description(array $artwork): string
{
    $parts = [$artwork['title'] . ' van Toos Boerlage'];
    if (!empty($artwork['technique'])) {
        $parts[] = (string) $artwork['technique'];
    }
    if (!empty($artwork['material'])) {
        $parts[] = 'op ' . mb_strtolower((string) $artwork['material']);
    }
    if (!empty($artwork['year'])) {
        $parts[] = (string) $artwork['year'];
    }

    $text = implode(', ', $parts) . '.';
    if (!empty($artwork['description'])) {
        $text .= ' ' . $artwork['description'];
    }
    if (mb_strlen($text) > 180) {
        $text = rtrim(mb_substr($text, 0, 177)) . '…';
    }

    return $text;
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return '/';
    }

    return $path;
}

function nav_current(string $href): string
{
    $path = rtrim(current_path(), '/') ?: '/';
    $href = rtrim($href, '/') ?: '/';
    if ($href === '/') {
        return $path === '/' ? ' aria-current="page"' : '';
    }
    if ($path === $href || str_starts_with($path, $href . '/')) {
        return ' aria-current="page"';
    }

    return '';
}

function collection_url(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    foreach ($params as $key => $value) {
        if ($value === null || $value === '' || ($key === 'sorteer' && $value === 'titel-op')) {
            unset($params[$key]);
        }
    }
    $query = http_build_query($params);

    return '/kunstwerken' . ($query !== '' ? '?' . $query : '');
}

function ini_to_bytes(string $value): int
{
    $value = trim($value);
    if ($value === '') {
        return 0;
    }
    $unit = strtolower(substr($value, -1));
    $number = (float) $value;

    return match ($unit) {
        'g' => (int) ($number * 1024 * 1024 * 1024),
        'm' => (int) ($number * 1024 * 1024),
        'k' => (int) ($number * 1024),
        default => (int) $number,
    };
}

function max_upload_bytes(): int
{
    $desired = 8 * 1024 * 1024;
    $ini = ini_to_bytes((string) ini_get('upload_max_filesize'));

    return $ini > 0 ? min($desired, $ini) : $desired;
}

function is_netherlands(string $country): bool
{
    $normalized = mb_strtolower(trim($country));

    return in_array($normalized, ['nederland', 'nl', 'the netherlands', 'netherlands', 'holland'], true);
}

function posted(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function validate_required(array &$errors, string $key, string $value, string $message, int $max): void
{
    if ($value === '') {
        $errors[$key] = $message;
    } elseif (mb_strlen($value) > $max) {
        $errors[$key] = 'Dit veld is te lang.';
    }
}

function validate_email(array &$errors, string $value, string $key = 'email'): void
{
    if ($value === '') {
        $errors[$key] = 'Vul je e-mailadres in.';
    } elseif (mb_strlen($value) > 190 || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $errors[$key] = 'Vul een geldig e-mailadres in.';
    }
}

function validate_phone(array &$errors, string $value, string $key = 'telefoon'): void
{
    if ($value === '') {
        return;
    }
    $digits = preg_replace('/\D+/', '', $value) ?? '';
    if (!preg_match('/^[0-9+\s().\/-]{8,40}$/', $value) || strlen($digits) < 8 || strlen($digits) > 15) {
        $errors[$key] = 'Vul een geldig telefoonnummer in, of laat het veld leeg.';
    }
}

function validate_postcode(array &$errors, string $postcode, string $country): void
{
    if ($postcode === '') {
        $errors['postcode'] = 'Vul de postcode in.';
        return;
    }
    if (mb_strlen($postcode) > 16) {
        $errors['postcode'] = 'De postcode is te lang.';
        return;
    }
    if (is_netherlands($country) && !preg_match('/^[1-9][0-9]{3}\s?[A-Za-z]{2}$/', $postcode)) {
        $errors['postcode'] = 'Gebruik een Nederlandse postcode, bijvoorbeeld 3221 KA.';
    } elseif (!is_netherlands($country) && mb_strlen($postcode) < 2) {
        $errors['postcode'] = 'Vul een geldige postcode in.';
    }
}

function honeypot_filled(): bool
{
    return posted('bedrijf') !== '';
}

/**
 * @return array{items: list<array<string, mixed>>, dropped: int}
 */
function cart_from_post(): array
{
    $raw = (string) ($_POST['cart'] ?? '');
    if ($raw === '' || strlen($raw) > 20000) {
        return ['items' => [], 'dropped' => $raw === '' ? 0 : 1];
    }

    try {
        $decoded = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return ['items' => [], 'dropped' => 1];
    }

    if (!is_array($decoded)) {
        return ['items' => [], 'dropped' => 1];
    }

    $items = [];
    $seen = [];
    $dropped = 0;
    foreach ($decoded as $entry) {
        $id = '';
        if (is_string($entry)) {
            $id = $entry;
        } elseif (is_array($entry) && isset($entry['id'])) {
            $id = (string) $entry['id'];
        }
        $artwork = $id !== '' ? artwork_by_id($id) : null;
        if ($artwork === null || isset($seen[$id]) || !can_purchase($artwork)) {
            if ($id !== '' || $entry !== null) {
                $dropped++;
            }
            continue;
        }
        $seen[$id] = true;
        $items[] = $artwork;
    }

    return ['items' => $items, 'dropped' => $dropped];
}

function cart_subtotal(array $items): float
{
    $sum = 0.0;
    foreach ($items as $item) {
        $sum += (float) ($item['price'] ?? 0);
    }

    return $sum;
}

function mailto_link(string $subject, string $body): string
{
    return 'mailto:' . ARTIST_EMAIL
        . '?subject=' . rawurlencode($subject)
        . '&body=' . rawurlencode($body);
}

/** @param list<array<string, mixed>> $items */
function purchase_mailto(array $fields, array $items): string
{
    $lines = [
        'Aanvraag om een aankoop af te ronden. Dit is geen geplaatste bestelling en geen betaling.',
        '',
        'Naam: ' . $fields['voornaam'] . ' ' . $fields['achternaam'],
        'E-mail: ' . $fields['email'],
    ];
    if ($fields['telefoon'] !== '') {
        $lines[] = 'Telefoon: ' . $fields['telefoon'];
    }
    $lines[] = 'Adres: ' . $fields['straat'] . ' ' . $fields['huisnummer'];
    $lines[] = $fields['postcode'] . ' ' . $fields['plaats'];
    $lines[] = 'Land: ' . $fields['land'];
    $lines[] = '';
    $lines[] = 'Werken:';
    foreach ($items as $item) {
        $lines[] = '- ' . $item['title'] . ' — ' . format_eur((float) $item['price']);
    }
    $lines[] = 'Subtotaal: ' . format_eur(cart_subtotal($items));
    $lines[] = 'Verzendkosten: nog niet vastgesteld.';

    return mailto_link('Aanvraag aankoop ' . SITE_NAME, implode("\n", $lines));
}

function optional_image_error(string $key = 'afbeelding'): ?string
{
    if (!isset($_FILES[$key]) || !is_array($_FILES[$key])) {
        return null;
    }

    $file = $_FILES[$key];
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $max = max_upload_bytes();
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        return 'Het bestand is te groot. Kies een afbeelding van maximaal ' . format_bytes($max) . '.';
    }
    if ($error !== UPLOAD_ERR_OK) {
        return 'Het bestand kon niet worden gelezen. Kies het opnieuw.';
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size < 1 || $size > $max) {
        return 'Het bestand is te groot. Kies een afbeelding van maximaal ' . format_bytes($max) . '.';
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $mime = '';
    if (is_file($tmp) && function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $tmp);
            finfo_close($finfo);
            $mime = is_string($detected) ? $detected : '';
        }
    }

    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mime, $allowed, true)) {
        return 'Kies een JPEG-, PNG-, WEBP- of GIF-bestand.';
    }

    return null;
}
