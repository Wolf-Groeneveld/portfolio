<?php

declare(strict_types=1);

function section_title(string $title, ?string $intro = null, string $id = ''): void
{
    $attr = $id !== '' ? ' id="' . e($id) . '"' : '';
    echo '<header class="section-heading">';
    echo '<h2' . $attr . '>' . e($title) . '</h2>';
    if ($intro !== null && $intro !== '') {
        echo '<p>' . e($intro) . '</p>';
    }
    echo '</header>';
}

function artwork_card(array $artwork): void
{
    $image = $artwork['images'][0];
    $href = '/kunstwerken/' . rawurlencode($artwork['slug']);
    echo '<article class="artwork-card">';
    echo '<a href="' . e($href) . '">';
    echo '<div class="card-media">';
    echo '<img src="' . e(asset($image['src'])) . '" alt="' . e($image['alt']) . '" width="800" height="640">';
    echo '</div>';
    echo '<div class="card-body">';
    echo '<h3>' . e($artwork['title']) . '</h3>';
    echo '<p class="card-meta">';
    echo '<span class="status status-' . e($artwork['status']) . '">' . e(status_label($artwork['status'])) . '</span>';
    if (($artwork['status'] ?? '') !== 'sold') {
        echo '<span class="card-price">' . e(price_label($artwork)) . '</span>';
    }
    echo '</p>';
    echo '</div>';
    echo '</a>';
    echo '</article>';
}

/** @param list<array<string, mixed>> $items */
function artwork_grid(array $items): void
{
    echo '<div class="artwork-grid">';
    foreach ($items as $artwork) {
        artwork_card($artwork);
    }
    echo '</div>';
}

function artwork_gallery(array $artwork): void
{
    $images = $artwork['images'];
    if (!is_array($images) || $images === []) {
        return;
    }
    $multiple = count($images) > 1;
    echo '<div class="product-gallery"' . ($multiple ? ' data-gallery' : '') . '>';
    $first = $images[0];
    echo '<div class="product-frame">';
    if ($multiple) {
        echo '<button type="button" class="gallery-open" data-gallery-open data-index="0">';
        echo '<img src="' . e(asset($first['src'])) . '" alt="' . e($first['alt']) . '" width="800" height="640">';
        echo '<span class="sr-only">Afbeelding vergroten</span>';
        echo '</button>';
    } else {
        echo '<img src="' . e(asset($first['src'])) . '" alt="' . e($first['alt']) . '" width="800" height="640">';
    }
    echo '<p class="placeholder-note">Beeld volgt. Dit vlak toont het kunstwerk niet.</p>';
    echo '</div>';
    if ($multiple) {
        echo '<ul class="thumbs">';
        foreach ($images as $index => $image) {
            $current = $index === 0 ? ' aria-current="true"' : '';
            echo '<li><button type="button" data-gallery-open data-index="' . (int) $index . '"' . $current . '>';
            echo '<img src="' . e(asset($image['src'])) . '" alt="">';
            echo '<span class="sr-only">Afbeelding ' . ((int) $index + 1) . ' van ' . count($images) . '</span>';
            echo '</button></li>';
        }
        echo '</ul>';
        $payload = [];
        foreach ($images as $image) {
            $payload[] = [
                'src' => asset($image['src']),
                'alt' => $image['alt'],
            ];
        }
        echo '<script type="application/json" class="gallery-data">';
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        echo '</script>';
    }
    echo '</div>';
}

function form_notice(string $title, string $text, ?string $actionHref = null, ?string $actionLabel = null): void
{
    echo '<div class="form-notice" role="status">';
    echo '<h2>' . e($title) . '</h2>';
    echo '<p>' . e($text) . '</p>';
    if ($actionHref !== null && $actionLabel !== null) {
        echo '<p><a class="btn btn-primary" href="' . e($actionHref) . '">' . e($actionLabel) . '</a></p>';
    }
    echo '</div>';
}

/**
 * @param array{name: string, label: string, type?: string, value?: string, error?: ?string, required?: bool, autocomplete?: ?string, hint?: ?string, maxlength?: int, inputmode?: ?string, rows?: int, id?: string} $opts
 */
function form_field(array $opts): void
{
    $name = $opts['name'];
    $id = $opts['id'] ?? $name;
    $label = $opts['label'];
    $type = $opts['type'] ?? 'text';
    $value = $opts['value'] ?? '';
    $error = $opts['error'] ?? null;
    $required = !empty($opts['required']);
    $hint = $opts['hint'] ?? null;
    $errorId = $error ? $id . '-error' : '';
    $hintId = $hint ? $id . '-hint' : '';
    $described = trim($hintId . ' ' . $errorId);

    echo '<div class="field' . ($error ? ' is-invalid' : '') . '">';
    echo '<label for="' . e($id) . '">' . e($label);
    if (!$required) {
        echo ' <span class="optional">(optioneel)</span>';
    }
    echo '</label>';
    if ($hint) {
        echo '<p class="field-hint" id="' . e($hintId) . '">' . e($hint) . '</p>';
    }

    $attrs = '';
    if (!empty($opts['autocomplete'])) {
        $attrs .= ' autocomplete="' . e((string) $opts['autocomplete']) . '"';
    }
    if ($required) {
        $attrs .= ' data-required="true"';
    }
    if (!empty($opts['maxlength'])) {
        $attrs .= ' maxlength="' . (int) $opts['maxlength'] . '"';
    }
    if (!empty($opts['inputmode'])) {
        $attrs .= ' inputmode="' . e((string) $opts['inputmode']) . '"';
    }
    if ($described !== '') {
        $attrs .= ' aria-describedby="' . e($described) . '"';
    }
    if ($error) {
        $attrs .= ' aria-invalid="true"';
    }

    if ($type === 'textarea') {
        $rows = (int) ($opts['rows'] ?? 6);
        echo '<textarea id="' . e($id) . '" name="' . e($name) . '" rows="' . $rows . '"' . $attrs . '>' . e($value) . '</textarea>';
    } else {
        echo '<input type="' . e($type) . '" id="' . e($id) . '" name="' . e($name) . '" value="' . e($value) . '"' . $attrs . '>';
    }
    if ($error) {
        echo '<p class="field-error" id="' . e($errorId) . '">' . e($error) . '</p>';
    }
    echo '</div>';
}

function honeypot_field(): void
{
    echo '<div class="hp" aria-hidden="true">';
    echo '<label for="bedrijf">Bedrijf</label>';
    echo '<input id="bedrijf" name="bedrijf" type="text" tabindex="-1" autocomplete="off" value="">';
    echo '</div>';
}

/** @param array<string, string> $state */
function filter_bar(array $state): void
{
    $categories = catalog_values('category');
    $techniques = catalog_values('technique');
    $statusOptions = [];
    $present = [];
    foreach (artworks() as $artwork) {
        $present[$artwork['status']] = true;
    }
    foreach (['available' => 'Beschikbaar', 'sold' => 'Verkocht', 'inquiry' => 'Op aanvraag'] as $value => $label) {
        if (!empty($present[$value])) {
            $statusOptions[$value] = $label;
        }
    }

    $showCategory = count($categories) >= 2;
    $showTechnique = count($techniques) >= 2;
    $showStatus = count($statusOptions) >= 2;
    $showPriceSort = catalog_has_prices();

    $active = [];
    if ($state['q'] !== '') {
        $active[] = ['label' => 'Zoeken: ' . $state['q'], 'href' => collection_url(['q' => ''])];
    }
    if ($state['categorie'] !== '') {
        $active[] = ['label' => $state['categorie'], 'href' => collection_url(['categorie' => ''])];
    }
    if ($state['techniek'] !== '') {
        $active[] = ['label' => $state['techniek'], 'href' => collection_url(['techniek' => ''])];
    }
    if ($state['beschikbaarheid'] !== '') {
        $active[] = [
            'label' => status_label($state['beschikbaarheid']),
            'href' => collection_url(['beschikbaarheid' => '']),
        ];
    }

    echo '<form class="filter-bar" method="get" action="/kunstwerken">';
    echo '<div class="filter-search">';
    form_field([
        'name' => 'q',
        'id' => 'collectie-q',
        'label' => 'Zoeken',
        'type' => 'search',
        'value' => $state['q'],
        'hint' => 'Zoek op titel of omschrijving.',
    ]);
    echo '</div>';
    echo '<div class="filter-controls">';
    if ($showCategory) {
        echo '<div class="field"><label for="categorie">Categorie</label>';
        echo '<select id="categorie" name="categorie">';
        echo '<option value="">Alle categorieën</option>';
        foreach ($categories as $category) {
            $selected = $state['categorie'] === $category ? ' selected' : '';
            echo '<option value="' . e($category) . '"' . $selected . '>' . e($category) . '</option>';
        }
        echo '</select></div>';
    }
    if ($showTechnique) {
        echo '<div class="field"><label for="techniek">Techniek</label>';
        echo '<select id="techniek" name="techniek">';
        echo '<option value="">Alle technieken</option>';
        foreach ($techniques as $technique) {
            $selected = $state['techniek'] === $technique ? ' selected' : '';
            echo '<option value="' . e($technique) . '"' . $selected . '>' . e($technique) . '</option>';
        }
        echo '</select></div>';
    }
    if ($showStatus) {
        echo '<div class="field"><label for="beschikbaarheid">Beschikbaarheid</label>';
        echo '<select id="beschikbaarheid" name="beschikbaarheid">';
        echo '<option value="">Alles</option>';
        foreach ($statusOptions as $value => $label) {
            $selected = $state['beschikbaarheid'] === $value ? ' selected' : '';
            echo '<option value="' . e($value) . '"' . $selected . '>' . e($label) . '</option>';
        }
        echo '</select></div>';
    }
    echo '<div class="field"><label for="sorteer">Sorteren</label>';
    echo '<select id="sorteer" name="sorteer">';
    $sorts = [
        'titel-op' => 'Titel A–Z',
        'titel-neer' => 'Titel Z–A',
    ];
    if ($showPriceSort) {
        $sorts['prijs-op'] = 'Prijs laag–hoog';
        $sorts['prijs-neer'] = 'Prijs hoog–laag';
    }
    foreach ($sorts as $value => $label) {
        $selected = $state['sorteer'] === $value ? ' selected' : '';
        echo '<option value="' . e($value) . '"' . $selected . '>' . e($label) . '</option>';
    }
    echo '</select></div>';
    echo '<div class="filter-actions"><button class="btn btn-primary" type="submit">Toon werken</button></div>';
    echo '</div>';
    echo '</form>';

    if ($active !== []) {
        echo '<div class="active-filters">';
        echo '<ul>';
        foreach ($active as $chip) {
            echo '<li><a href="' . e($chip['href']) . '">' . e($chip['label']) . ' <span aria-hidden="true">×</span><span class="sr-only">, filter wissen</span></a></li>';
        }
        echo '</ul>';
        echo '<a class="text-link" href="/kunstwerken">Wis filters</a>';
        echo '</div>';
    }
}

function newsletter_section(?string $notice, string $emailValue, ?string $error): void
{
    echo '<section class="section newsletter" id="nieuwsbrief" aria-labelledby="nieuwsbrief-titel">';
    echo '<div class="container narrow">';
    section_title(
        'Nieuwsbrief',
        'Op de eerdere site was een nieuwsbrief aangekondigd voor nieuw werk en exposities. Die inschrijving is hier nog niet gekoppeld.',
        'nieuwsbrief-titel'
    );
    if ($notice === 'invalid') {
        form_notice('Niet ingeschreven', 'Vul een geldig e-mailadres in. Er is niets opgeslagen.');
    } elseif ($notice === 'none') {
        form_notice('Niet ingeschreven', 'Je bent niet toegevoegd aan een lijst. Er is nog geen werkende nieuwsbriefkoppeling.');
    }
    echo '<form class="stack-form" method="post" action="/#nieuwsbrief" novalidate>';
    echo '<input type="hidden" name="form" value="newsletter">';
    honeypot_field();
    form_field([
        'name' => 'email',
        'id' => 'nieuwsbrief-email',
        'label' => 'E-mailadres',
        'type' => 'email',
        'value' => $emailValue,
        'required' => true,
        'autocomplete' => 'email',
        'error' => $error,
        'maxlength' => 190,
    ]);
    echo '<button class="btn btn-secondary" type="submit">Inschrijven</button>';
    echo '</form>';
    echo '</div></section>';
}

/** @return list<array{id: string, slug: string, title: string, status: string, currency: string, price?: int|float}> */
function public_catalog_index(): array
{
    $index = [];
    foreach (artworks() as $artwork) {
        $row = [
            'id' => $artwork['id'],
            'slug' => $artwork['slug'],
            'title' => $artwork['title'],
            'status' => $artwork['status'],
            'currency' => 'EUR',
        ];
        if (isset($artwork['price'])) {
            $row['price'] = $artwork['price'];
        }
        $index[] = $row;
    }

    return $index;
}
