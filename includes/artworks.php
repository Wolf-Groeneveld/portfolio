<?php

declare(strict_types=1);

/**
 * Centrale catalogus.
 * widthCm en heightCm blijven leeg: de bron vermeldt maten als reeks
 * zonder aan te geven welk getal de breedte is. Die reeks staat in dimensionsLabel.
 * Afbeeldingen zijn een neutrale plaatsaanduiding, geen weergave van het werk.
 *
 * @return list<array<string, mixed>>
 */
function artworks(): array
{
    static $items = null;
    if ($items !== null) {
        return $items;
    }

    $path = __DIR__ . '/catalog.json';
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('Catalogus ontbreekt.');
    }

    $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($decoded)) {
        throw new RuntimeException('Catalogus is ongeldig.');
    }

    $slugs = [];
    $ids = [];
    foreach ($decoded as $artwork) {
        if (!is_array($artwork)) {
            throw new RuntimeException('Catalogus bevat een ongeldig werk.');
        }
        foreach (['id', 'slug', 'title', 'artist', 'currency', 'status', 'images'] as $key) {
            if (!isset($artwork[$key]) || $artwork[$key] === '' || $artwork[$key] === []) {
                throw new RuntimeException('Catalogus mist veld ' . $key . '.');
            }
        }
        if ($artwork['artist'] !== 'Toos Boerlage' || $artwork['currency'] !== 'EUR') {
            throw new RuntimeException('Catalogus bevat een ongeldige kunstenaar of valuta.');
        }
        if (!in_array($artwork['status'], ['available', 'sold', 'inquiry'], true)) {
            throw new RuntimeException('Onbekende status in de catalogus.');
        }
        if (isset($artwork['price']) && !is_int($artwork['price']) && !is_float($artwork['price'])) {
            throw new RuntimeException('Ongeldige prijs in de catalogus.');
        }
        if (isset($ids[$artwork['id']]) || isset($slugs[$artwork['slug']])) {
            throw new RuntimeException('Dubbele id of slug in de catalogus.');
        }
        $ids[$artwork['id']] = true;
        $slugs[$artwork['slug']] = true;
    }

    $items = $decoded;

    return $items;
}

function artwork_by_slug(string $slug): ?array
{
    foreach (artworks() as $artwork) {
        if ($artwork['slug'] === $slug) {
            return $artwork;
        }
    }

    return null;
}

function artwork_by_id(string $id): ?array
{
    foreach (artworks() as $artwork) {
        if ($artwork['id'] === $id) {
            return $artwork;
        }
    }

    return null;
}

/** @return list<array<string, mixed>> */
function featured_artworks(int $limit = 4): array
{
    $items = array_values(array_filter(
        artworks(),
        static fn (array $artwork): bool => !empty($artwork['featured'])
            && ($artwork['status'] ?? '') === 'available'
            && isset($artwork['price'])
    ));

    usort($items, static function (array $a, array $b): int {
        return ($b['year'] ?? 0) <=> ($a['year'] ?? 0);
    });

    return array_slice($items, 0, $limit);
}

/** @return list<array<string, mixed>> */
function related_artworks(array $artwork, int $limit = 4): array
{
    $category = $artwork['category'] ?? null;
    if (!is_string($category) || $category === '') {
        return [];
    }

    $related = [];
    foreach (artworks() as $candidate) {
        if ($candidate['id'] === $artwork['id']) {
            continue;
        }
        if (($candidate['category'] ?? null) !== $category) {
            continue;
        }
        $related[] = $candidate;
        if (count($related) >= $limit) {
            break;
        }
    }

    return $related;
}

function catalog_has_prices(): bool
{
    foreach (artworks() as $artwork) {
        if (isset($artwork['price'])) {
            return true;
        }
    }

    return false;
}

/** @return list<string> */
function catalog_values(string $key): array
{
    $values = [];
    foreach (artworks() as $artwork) {
        $value = $artwork[$key] ?? null;
        if (is_string($value) && $value !== '') {
            $values[$value] = true;
        }
    }

    $list = array_keys($values);
    natcasesort($list);

    return array_values($list);
}

/**
 * @param array<string, string> $query
 * @return array{items: list<array<string, mixed>>, state: array<string, string>}
 */
function filter_artworks(array $query): array
{
    $sorts = ['titel-op', 'titel-neer'];
    if (catalog_has_prices()) {
        $sorts[] = 'prijs-op';
        $sorts[] = 'prijs-neer';
    }

    $categories = catalog_values('category');
    $techniques = catalog_values('technique');
    $statuses = [];
    foreach (artworks() as $artwork) {
        $statuses[$artwork['status']] = true;
    }

    $state = [
        'q' => trim($query['q'] ?? ''),
        'categorie' => trim($query['categorie'] ?? ''),
        'techniek' => trim($query['techniek'] ?? ''),
        'beschikbaarheid' => trim($query['beschikbaarheid'] ?? ''),
        'sorteer' => trim($query['sorteer'] ?? 'titel-op'),
    ];

    if (count($categories) < 2) {
        $state['categorie'] = '';
    } elseif (!in_array($state['categorie'], $categories, true)) {
        $state['categorie'] = '';
    }

    if (count($techniques) < 2) {
        $state['techniek'] = '';
    } elseif (!in_array($state['techniek'], $techniques, true)) {
        $state['techniek'] = '';
    }

    $allowedStatus = [];
    foreach (['available', 'sold', 'inquiry'] as $status) {
        if (!empty($statuses[$status])) {
            $allowedStatus[] = $status;
        }
    }
    if (count($allowedStatus) < 2 || !in_array($state['beschikbaarheid'], $allowedStatus, true)) {
        $state['beschikbaarheid'] = '';
    }

    if (!in_array($state['sorteer'], $sorts, true)) {
        $state['sorteer'] = 'titel-op';
    }

    $q = mb_strtolower($state['q']);
    $items = [];
    foreach (artworks() as $artwork) {
        if ($state['categorie'] !== '' && ($artwork['category'] ?? '') !== $state['categorie']) {
            continue;
        }
        if ($state['techniek'] !== '' && ($artwork['technique'] ?? '') !== $state['techniek']) {
            continue;
        }
        if ($state['beschikbaarheid'] !== '' && ($artwork['status'] ?? '') !== $state['beschikbaarheid']) {
            continue;
        }
        if ($q !== '') {
            $haystack = mb_strtolower($artwork['title'] . ' ' . ($artwork['description'] ?? ''));
            if (!str_contains($haystack, $q)) {
                continue;
            }
        }
        $items[] = $artwork;
    }

    $sort = $state['sorteer'];
    usort($items, static function (array $a, array $b) use ($sort): int {
        if ($sort === 'prijs-op' || $sort === 'prijs-neer') {
            $aPrice = $a['price'] ?? null;
            $bPrice = $b['price'] ?? null;
            if ($aPrice === null && $bPrice === null) {
                return strnatcasecmp($a['title'], $b['title']);
            }
            if ($aPrice === null) {
                return 1;
            }
            if ($bPrice === null) {
                return -1;
            }
            if ((float) $aPrice !== (float) $bPrice) {
                $cmp = (float) $aPrice <=> (float) $bPrice;

                return $sort === 'prijs-op' ? $cmp : -$cmp;
            }
        }

        $title = strnatcasecmp($a['title'], $b['title']);

        return $sort === 'titel-neer' ? -$title : $title;
    });

    return ['items' => $items, 'state' => $state];
}
