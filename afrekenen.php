<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$fields = [
    'voornaam' => '',
    'achternaam' => '',
    'email' => '',
    'telefoon' => '',
    'straat' => '',
    'huisnummer' => '',
    'postcode' => '',
    'plaats' => '',
    'land' => 'Nederland',
];
$errors = [];
$cartItems = [];
$dropped = 0;
$ready = false;
$mailto = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach (array_keys($fields) as $key) {
        $fields[$key] = posted($key);
    }
    if ($fields['land'] === '') {
        $fields['land'] = 'Nederland';
    }

    $cart = cart_from_post();
    $cartItems = $cart['items'];
    $dropped = $cart['dropped'];

    if (!honeypot_filled()) {
        validate_required($errors, 'voornaam', $fields['voornaam'], 'Vul je voornaam in.', 80);
        validate_required($errors, 'achternaam', $fields['achternaam'], 'Vul je achternaam in.', 80);
        validate_email($errors, $fields['email']);
        validate_phone($errors, $fields['telefoon']);
        validate_required($errors, 'straat', $fields['straat'], 'Vul de straat in.', 120);
        validate_required($errors, 'huisnummer', $fields['huisnummer'], 'Vul het huisnummer in.', 20);
        validate_postcode($errors, $fields['postcode'], $fields['land']);
        validate_required($errors, 'plaats', $fields['plaats'], 'Vul de plaats in.', 80);
        validate_required($errors, 'land', $fields['land'], 'Vul het land in.', 80);
        if ($cartItems === []) {
            $errors['cart'] = 'Je winkelwagen is leeg, of de gekozen werken zijn niet meer te koop.';
        }
        if ($errors === []) {
            $ready = true;
            $mailto = purchase_mailto($fields, $cartItems);
        }
    }
}

$page = [
    'title' => 'Afrekenen | Toos Boerlage',
    'description' => 'Rond een aanvraag af voor werken van Toos Boerlage. Op deze site wordt nog niet betaald.',
    'path' => '/afrekenen',
    'robots' => 'noindex,follow',
];

require __DIR__ . '/includes/header.php';
?>

<section class="page-intro">
    <div class="container narrow">
        <h1>Afrekenen</h1>
        <p class="lede">Dit formulier verwerkt geen definitieve bestelling en start geen betaling. Er is nog geen betaalprovider gekoppeld. Na controle kun je zelf een e-mail sturen om de aankoop af te ronden.</p>
    </div>
</section>

<section class="section section-tight">
    <div class="container checkout-layout">
        <div>
            <?php if ($ready && $mailto !== null): ?>
                <?php form_notice(
                    'Nog niet besteld',
                    'Je gegevens zijn gecontroleerd, maar er is geen bestelling geplaatst en er is niet betaald. Open je e-mailprogramma en verstuur het bericht zelf. Pas daarna kan het atelier de aankoop met je afstemmen.',
                    $mailto,
                    'Open e-mail om de aankoop af te ronden'
                ); ?>
            <?php elseif (honeypot_filled() && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'): ?>
                <?php form_notice('Niet verzonden', 'Het formulier is niet verzonden. Er is geen bestelling geplaatst.'); ?>
            <?php endif; ?>

            <?php if (!payment_can_charge()): ?>
                <p class="form-aside">Betaalmethode: nog niet beschikbaar. Mollie of Stripe kan later worden gekoppeld, zonder sleutels in deze pagina.</p>
            <?php endif; ?>

            <form class="stack-form" method="post" action="/afrekenen" id="checkout-form" novalidate>
                <input type="hidden" name="cart" id="cart-payload" value="">
                <?php honeypot_field(); ?>
                <?php if (!empty($errors['cart'])): ?>
                    <p class="field-error" id="cart-error"><?= e($errors['cart']) ?></p>
                <?php endif; ?>
                <?php if ($dropped > 0 && !$ready): ?>
                    <p class="field-hint">Een of meer regels zijn overgeslagen omdat het werk ontbreekt of niet meer te koop is.</p>
                <?php endif; ?>
                <div class="form-row">
                    <?php form_field(['name' => 'voornaam', 'label' => 'Voornaam', 'value' => $fields['voornaam'], 'required' => true, 'autocomplete' => 'given-name', 'error' => $errors['voornaam'] ?? null, 'maxlength' => 80]); ?>
                    <?php form_field(['name' => 'achternaam', 'label' => 'Achternaam', 'value' => $fields['achternaam'], 'required' => true, 'autocomplete' => 'family-name', 'error' => $errors['achternaam'] ?? null, 'maxlength' => 80]); ?>
                </div>
                <div class="form-row">
                    <?php form_field(['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'value' => $fields['email'], 'required' => true, 'autocomplete' => 'email', 'error' => $errors['email'] ?? null, 'maxlength' => 190]); ?>
                    <?php form_field(['name' => 'telefoon', 'label' => 'Telefoon', 'type' => 'tel', 'value' => $fields['telefoon'], 'autocomplete' => 'tel', 'error' => $errors['telefoon'] ?? null, 'maxlength' => 40]); ?>
                </div>
                <div class="form-row form-row-street">
                    <?php form_field(['name' => 'straat', 'label' => 'Straat', 'value' => $fields['straat'], 'required' => true, 'autocomplete' => 'address-line1', 'error' => $errors['straat'] ?? null, 'maxlength' => 120]); ?>
                    <?php form_field(['name' => 'huisnummer', 'label' => 'Huisnummer', 'value' => $fields['huisnummer'], 'required' => true, 'error' => $errors['huisnummer'] ?? null, 'maxlength' => 20]); ?>
                </div>
                <div class="form-row">
                    <?php form_field(['name' => 'postcode', 'label' => 'Postcode', 'value' => $fields['postcode'], 'required' => true, 'autocomplete' => 'postal-code', 'error' => $errors['postcode'] ?? null, 'maxlength' => 16]); ?>
                    <?php form_field(['name' => 'plaats', 'label' => 'Plaats', 'value' => $fields['plaats'], 'required' => true, 'autocomplete' => 'address-level2', 'error' => $errors['plaats'] ?? null, 'maxlength' => 80]); ?>
                </div>
                <?php form_field(['name' => 'land', 'label' => 'Land', 'value' => $fields['land'], 'required' => true, 'autocomplete' => 'country-name', 'error' => $errors['land'] ?? null, 'maxlength' => 80]); ?>
                <button class="btn btn-primary" type="submit">Controleer gegevens</button>
            </form>
        </div>
        <aside class="summary" aria-labelledby="samenvatting-titel">
            <h2 id="samenvatting-titel">Je selectie</h2>
            <div id="checkout-summary" data-checkout-summary>
                <?php if ($cartItems !== []): ?>
                    <ul class="summary-list">
                        <?php foreach ($cartItems as $item): ?>
                            <li>
                                <a href="/kunstwerken/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a>
                                <span><?= e(format_eur((float) $item['price'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="subtotal"><span>Subtotaal</span> <strong><?= e(format_eur(cart_subtotal($cartItems))) ?></strong></p>
                <?php else: ?>
                    <p class="empty-copy">De winkelwagen wordt in je browser bewaard. Zodra je een werk toevoegt, verschijnt het hier.</p>
                <?php endif; ?>
            </div>
            <p class="cart-shipping">Er worden geen verzendkosten getoond. Die zijn nog niet vastgesteld.</p>
            <p><a href="/kunstwerken">Terug naar de collectie</a></p>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
