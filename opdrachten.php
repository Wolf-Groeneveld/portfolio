<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$fields = [
    'naam' => '',
    'email' => '',
    'telefoon' => '',
    'afmetingen' => '',
    'kleuren' => '',
    'budget' => '',
    'beschrijving' => '',
];
$errors = [];
$ready = false;
$mailto = null;
$maxLabel = format_bytes(max_upload_bytes());

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach (array_keys($fields) as $key) {
        $fields[$key] = posted($key);
    }
    $imageError = optional_image_error('afbeelding');
    if ($imageError !== null) {
        $errors['afbeelding'] = $imageError;
    }

    if (!honeypot_filled()) {
        validate_required($errors, 'naam', $fields['naam'], 'Vul je naam in.', 120);
        validate_email($errors, $fields['email']);
        validate_phone($errors, $fields['telefoon']);
        validate_required($errors, 'afmetingen', $fields['afmetingen'], 'Vul de gewenste afmetingen in.', 160);
        validate_required($errors, 'kleuren', $fields['kleuren'], 'Vul de gewenste kleuren in.', 160);
        validate_required($errors, 'budget', $fields['budget'], 'Vul een budget in.', 80);
        validate_required($errors, 'beschrijving', $fields['beschrijving'], 'Beschrijf de opdracht.', 5000);
        if ($errors === []) {
            $ready = true;
            $body = "Aanvraag voor een mogelijke opdracht. Dit bericht is nog niet via de site verzonden.\n\n"
                . 'Naam: ' . $fields['naam'] . "\n"
                . 'E-mail: ' . $fields['email'] . "\n"
                . ($fields['telefoon'] !== '' ? 'Telefoon: ' . $fields['telefoon'] . "\n" : '')
                . 'Afmetingen: ' . $fields['afmetingen'] . "\n"
                . 'Kleuren: ' . $fields['kleuren'] . "\n"
                . 'Budget: ' . $fields['budget'] . "\n\n"
                . $fields['beschrijving'] . "\n\n"
                . "Een gekozen afbeelding zit niet in deze e-mail. Stuur die desgewenst als bijlage mee.";
            $mailto = mailto_link('Opdracht aanvraag ' . SITE_NAME, $body);
        }
    }
}

$page = [
    'title' => 'Opdrachten | Toos Boerlage',
    'description' => 'Vraag een mogelijk werk op maat aan bij Toos Boerlage. Het formulier verzendt de aanvraag nog niet zelf.',
    'path' => '/opdrachten',
];

require __DIR__ . '/includes/header.php';
?>

<section class="page-intro">
    <div class="container narrow">
        <h1>Opdrachten</h1>
        <p class="lede">Toos Boerlage werkt in opdracht. Met dit formulier bereid je een aanvraag voor een mogelijk werk op maat voor. De site verstuurt de aanvraag nog niet en bewaart geen bestand.</p>
    </div>
</section>

<section class="section section-tight">
    <div class="container narrow">
        <?php if ($ready && $mailto !== null): ?>
            <?php form_notice(
                'Aanvraag nog niet verzonden',
                'De gegevens zijn gecontroleerd, maar er is geen aanvraag ontvangen. Open je e-mailprogramma en verstuur het bericht zelf. Een gekozen afbeelding moet je apart als bijlage toevoegen.',
                $mailto,
                'Open e-mail voor de opdracht'
            ); ?>
        <?php elseif (honeypot_filled() && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'): ?>
            <?php form_notice('Niet verzonden', 'Het formulier is niet verzonden. Er is geen aanvraag ontvangen.'); ?>
        <?php endif; ?>

        <form class="stack-form" method="post" action="/opdrachten" enctype="multipart/form-data" id="request-form" novalidate>
            <?php honeypot_field(); ?>
            <?php form_field(['name' => 'naam', 'label' => 'Naam', 'value' => $fields['naam'], 'required' => true, 'autocomplete' => 'name', 'error' => $errors['naam'] ?? null, 'maxlength' => 120]); ?>
            <div class="form-row">
                <?php form_field(['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'value' => $fields['email'], 'required' => true, 'autocomplete' => 'email', 'error' => $errors['email'] ?? null, 'maxlength' => 190]); ?>
                <?php form_field(['name' => 'telefoon', 'label' => 'Telefoon', 'type' => 'tel', 'value' => $fields['telefoon'], 'autocomplete' => 'tel', 'error' => $errors['telefoon'] ?? null, 'maxlength' => 40]); ?>
            </div>
            <?php form_field(['name' => 'afmetingen', 'label' => 'Gewenste afmetingen', 'value' => $fields['afmetingen'], 'required' => true, 'error' => $errors['afmetingen'] ?? null, 'maxlength' => 160]); ?>
            <?php form_field(['name' => 'kleuren', 'label' => 'Kleuren', 'value' => $fields['kleuren'], 'required' => true, 'error' => $errors['kleuren'] ?? null, 'maxlength' => 160]); ?>
            <?php form_field(['name' => 'budget', 'label' => 'Budget', 'value' => $fields['budget'], 'required' => true, 'error' => $errors['budget'] ?? null, 'maxlength' => 80]); ?>
            <?php form_field(['name' => 'beschrijving', 'label' => 'Beschrijving', 'type' => 'textarea', 'value' => $fields['beschrijving'], 'required' => true, 'error' => $errors['beschrijving'] ?? null, 'maxlength' => 5000, 'rows' => 7]); ?>

            <div class="field<?= isset($errors['afbeelding']) ? ' is-invalid' : '' ?>">
                <label for="afbeelding">Afbeelding <span class="optional">(optioneel)</span></label>
                <p class="field-hint" id="afbeelding-hint">JPEG, PNG, WEBP of GIF. Maximaal <?= e($maxLabel) ?>. Het bestand wordt niet opgeslagen en niet meegestuurd.</p>
                <input id="afbeelding" name="afbeelding" type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-max-bytes="<?= (int) max_upload_bytes() ?>" aria-describedby="afbeelding-hint<?= isset($errors['afbeelding']) ? ' afbeelding-error' : '' ?>"<?= isset($errors['afbeelding']) ? ' aria-invalid="true"' : '' ?>>
                <p class="file-chosen" data-file-chosen hidden>
                    <span data-file-name></span>
                    <button type="button" class="text-button" data-file-clear>Verwijder bestand</button>
                </p>
                <?php if (isset($errors['afbeelding'])): ?>
                    <p class="field-error" id="afbeelding-error"><?= e($errors['afbeelding']) ?></p>
                <?php endif; ?>
            </div>
            <button class="btn btn-primary" type="submit">Controleer aanvraag</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
