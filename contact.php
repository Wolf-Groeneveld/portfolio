<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$fields = [
    'naam' => '',
    'email' => '',
    'onderwerp' => mb_substr(trim((string) ($_GET['onderwerp'] ?? '')), 0, 140),
    'bericht' => '',
];
$errors = [];
$ready = false;
$mailto = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach (array_keys($fields) as $key) {
        $fields[$key] = posted($key);
    }
    if (!honeypot_filled()) {
        validate_required($errors, 'naam', $fields['naam'], 'Vul je naam in.', 120);
        validate_email($errors, $fields['email']);
        validate_required($errors, 'onderwerp', $fields['onderwerp'], 'Vul een onderwerp in.', 140);
        validate_required($errors, 'bericht', $fields['bericht'], 'Schrijf een bericht.', 5000);
        if ($errors === []) {
            $ready = true;
            $body = "Bericht via de site. Dit is nog niet verzonden.\n\n"
                . 'Naam: ' . $fields['naam'] . "\n"
                . 'E-mail: ' . $fields['email'] . "\n"
                . 'Onderwerp: ' . $fields['onderwerp'] . "\n\n"
                . $fields['bericht'];
            $mailto = mailto_link($fields['onderwerp'], $body);
        }
    }
}

$page = [
    'title' => 'Contact | Toos Boerlage',
    'description' => 'Contactgegevens van Toos Boerlage in Hellevoetsluis. Het contactformulier verzendt nog geen bericht.',
    'path' => '/contact',
];

require __DIR__ . '/includes/header.php';
?>

<section class="page-intro">
    <div class="container narrow">
        <h1>Contact</h1>
        <p class="lede">Voor een werk, een opdracht of de cursussen kun je contact opnemen. Het formulier op deze pagina verzendt nog niets.</p>
    </div>
</section>

<section class="section section-tight">
    <div class="container contact-layout">
        <div>
            <?php if ($ready && $mailto !== null): ?>
                <?php form_notice(
                    'Bericht nog niet verzonden',
                    'De gegevens zijn gecontroleerd, maar het bericht is niet ontvangen. Open je e-mailprogramma en verstuur het zelf.',
                    $mailto,
                    'Open e-mail'
                ); ?>
            <?php elseif (honeypot_filled() && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'): ?>
                <?php form_notice('Niet verzonden', 'Het formulier is niet verzonden. Er is geen bericht ontvangen.'); ?>
            <?php endif; ?>

            <form class="stack-form" method="post" action="/contact" id="contact-form" novalidate>
                <?php honeypot_field(); ?>
                <?php form_field(['name' => 'naam', 'label' => 'Naam', 'value' => $fields['naam'], 'required' => true, 'autocomplete' => 'name', 'error' => $errors['naam'] ?? null, 'maxlength' => 120]); ?>
                <?php form_field(['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'value' => $fields['email'], 'required' => true, 'autocomplete' => 'email', 'error' => $errors['email'] ?? null, 'maxlength' => 190]); ?>
                <?php form_field(['name' => 'onderwerp', 'label' => 'Onderwerp', 'value' => $fields['onderwerp'], 'required' => true, 'error' => $errors['onderwerp'] ?? null, 'maxlength' => 140]); ?>
                <?php form_field(['name' => 'bericht', 'label' => 'Bericht', 'type' => 'textarea', 'value' => $fields['bericht'], 'required' => true, 'error' => $errors['bericht'] ?? null, 'maxlength' => 5000, 'rows' => 7]); ?>
                <button class="btn btn-primary" type="submit">Controleer bericht</button>
            </form>
        </div>
        <aside class="contact-card">
            <h2>Gegevens</h2>
            <address>
                <?= e(SITE_NAME) ?><br>
                <?= e(ARTIST_STREET) ?><br>
                <?= e(ARTIST_POSTCODE) ?> <?= e(ARTIST_CITY) ?>
            </address>
            <p><a href="tel:<?= e(ARTIST_PHONE_TEL) ?>"><?= e(ARTIST_PHONE_DISPLAY) ?></a></p>
            <p><a href="mailto:<?= e(ARTIST_EMAIL) ?>"><?= e(ARTIST_EMAIL) ?></a></p>
            <ul>
                <?php foreach (ARTIST_WEBSITES as $site): ?>
                    <li><a href="<?= e($site['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($site['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
