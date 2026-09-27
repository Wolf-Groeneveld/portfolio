<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$slug = (string) ($_GET['slug'] ?? '');
$artwork = $slug !== '' ? artwork_by_slug($slug) : null;

if ($artwork === null) {
    http_response_code(404);
    $page = [
        'title' => 'Werk niet gevonden | Toos Boerlage',
        'description' => 'Dit werk staat niet in de collectie van Toos Boerlage.',
        'path' => '/kunstwerken',
        'robots' => 'noindex,follow',
    ];
    require __DIR__ . '/includes/header.php';
    echo '<section class="page-intro"><div class="container narrow">';
    echo '<h1>Werk niet gevonden</h1>';
    echo '<p class="lede">Dit adres hoort niet bij een werk in de collectie.</p>';
    echo '<p><a class="btn btn-primary" href="/kunstwerken">Naar de collectie</a></p>';
    echo '</div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$facts = [];
if (!empty($artwork['technique'])) {
    $facts[] = ['Techniek', (string) $artwork['technique']];
}
if (!empty($artwork['material'])) {
    $facts[] = ['Materiaal', (string) $artwork['material']];
}
if (!empty($artwork['dimensionsLabel'])) {
    $facts[] = ['Afmetingen', (string) $artwork['dimensionsLabel']];
}
if (!empty($artwork['year'])) {
    $facts[] = ['Jaar', (string) $artwork['year']];
}

$page = [
    'title' => $artwork['title'] . ' | Toos Boerlage',
    'description' => artwork_meta_description($artwork),
    'path' => '/kunstwerken/' . $artwork['slug'],
    'productPrice' => $artwork['price'] ?? null,
];

$related = related_artworks($artwork, 4);
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'VisualArtwork',
    'name' => $artwork['title'],
    'artist' => [
        '@type' => 'Person',
        'name' => 'Toos Boerlage',
    ],
    'url' => site_url('kunstwerken/' . $artwork['slug']),
];
if (!empty($artwork['technique'])) {
    $jsonLd['artMedium'] = $artwork['technique'];
}
if (!empty($artwork['material'])) {
    $jsonLd['artworkSurface'] = $artwork['material'];
}
if (!empty($artwork['year'])) {
    $jsonLd['dateCreated'] = (string) $artwork['year'];
}
if (can_purchase($artwork)) {
    $jsonLd['offers'] = [
        '@type' => 'Offer',
        'price' => $artwork['price'],
        'priceCurrency' => 'EUR',
        'availability' => 'https://schema.org/InStock',
    ];
} elseif (($artwork['status'] ?? '') === 'sold') {
    $jsonLd['offers'] = [
        '@type' => 'Offer',
        'availability' => 'https://schema.org/SoldOut',
        'priceCurrency' => 'EUR',
    ];
}

require __DIR__ . '/includes/header.php';
?>

<article class="section product">
    <div class="container">
        <p class="back-link"><a href="/kunstwerken">Terug naar de collectie</a></p>
        <div class="product-layout">
            <?php artwork_gallery($artwork); ?>
            <div class="product-info">
                <h1><?= e($artwork['title']) ?></h1>
                <p class="product-artist">Toos Boerlage</p>
                <p class="product-price"><?= e(price_label($artwork)) ?></p>
                <p class="status status-<?= e($artwork['status']) ?>"><?= e(status_label($artwork['status'])) ?></p>
                <?php if (!empty($artwork['description'])): ?>
                    <div class="prose"><p><?= e($artwork['description']) ?></p></div>
                <?php endif; ?>
                <?php if ($facts !== []): ?>
                    <dl class="facts">
                        <?php foreach ($facts as [$label, $value]): ?>
                            <div>
                                <dt><?= e($label) ?></dt>
                                <dd><?= e($value) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>
                <?php if (can_purchase($artwork)): ?>
                    <button type="button" class="btn btn-primary" data-add-to-cart data-id="<?= e($artwork['id']) ?>">In winkelwagen</button>
                    <p class="field-hint" data-cart-feedback hidden></p>
                <?php elseif (($artwork['status'] ?? '') === 'sold'): ?>
                    <p class="sold-note"><strong>Verkocht.</strong> Dit werk kan niet meer worden gekocht.</p>
                <?php else: ?>
                    <p>Prijs op aanvraag. Neem contact op voor beschikbaarheid.</p>
                    <p><a class="btn btn-secondary" href="/contact?onderwerp=<?= rawurlencode($artwork['title']) ?>">Neem contact op</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>

<?php if ($related !== []): ?>
<section class="section" aria-labelledby="verwant-titel">
    <div class="container">
        <?php section_title('Verwante werken', 'Meer uit dezelfde categorie.', 'verwant-titel'); ?>
        <?php artwork_grid($related); ?>
    </div>
</section>
<?php endif; ?>

<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
