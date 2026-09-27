<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$newsletterNotice = null;
$newsletterEmail = '';
$newsletterError = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && posted('form') === 'newsletter') {
    $newsletterEmail = posted('email');
    if (honeypot_filled()) {
        $newsletterNotice = 'none';
        $newsletterEmail = '';
    } else {
        $errors = [];
        validate_email($errors, $newsletterEmail);
        if ($errors) {
            $newsletterNotice = 'invalid';
            $newsletterError = $errors['email'];
        } else {
            $newsletterNotice = 'none';
        }
    }
}

$page = [
    'title' => SITE_TITLE,
    'description' => SITE_DESCRIPTION,
    'path' => '/',
];

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <h1>
                <span class="hero-name">Toos Boerlage</span>
                <span class="hero-kop">Kunst met kleur, karakter en gevoel.</span>
            </h1>
            <p class="lede">Originele schilderijen uit Hellevoetsluis, in wisselende stijlen en technieken. De gegevens bij elk werk komen uit de bestaande collectie. Afbeeldingen worden later toegevoegd.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="/kunstwerken">Bekijk kunstwerken</a>
                <a class="btn btn-secondary" href="/over">Ontdek Toos</a>
            </div>
        </div>
        <figure class="hero-figure">
            <img src="<?= e(asset('assets/img/placeholder.svg')) ?>" alt="Tijdelijke plaatsaanduiding. Er staat nog geen kunstwerk in dit vlak." width="800" height="640">
            <figcaption>Beeld volgt</figcaption>
        </figure>
    </div>
</section>

<section class="section" aria-labelledby="uitgelicht-titel">
    <div class="container">
        <?php
        $featured = featured_artworks(4);
        section_title('Uitgelichte werken', 'Een selectie van beschikbare werken uit de collectie.', 'uitgelicht-titel');
        if ($featured === []) {
            echo '<p class="empty-state">Er zijn nog geen uitgelichte werken met een bekende prijs.</p>';
        } else {
            artwork_grid($featured);
        }
        ?>
    </div>
</section>

<section class="section section-split" aria-labelledby="wereld-titel">
    <div class="container split">
        <div>
            <?php section_title('Ontdek de wereld van Toos', null, 'wereld-titel'); ?>
            <p><?= e(bio_excerpt()) ?></p>
            <p><a class="btn btn-secondary" href="/over">Lees meer over Toos</a></p>
        </div>
        <div class="split-panel">
            <?php section_title('Werk in opdracht'); ?>
            <p>Toos Boerlage maakt ook werk in opdracht, naast haar vrije schilderijen. Je kunt een aanvraag voorbereiden met gewenste afmetingen, kleuren en budget.</p>
            <p><a class="btn btn-primary" href="/opdrachten">Vraag een opdracht aan</a></p>
        </div>
    </div>
</section>

<?php
newsletter_section($newsletterNotice, $newsletterEmail, $newsletterError);
require __DIR__ . '/includes/footer.php';
