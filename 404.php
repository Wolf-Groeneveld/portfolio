<?php

declare(strict_types=1);

http_response_code(404);

require_once __DIR__ . '/includes/bootstrap.php';

$page = [
    'title' => 'Pagina niet gevonden | Toos Boerlage',
    'description' => 'Deze pagina bestaat niet op de site van Toos Boerlage.',
    'path' => '/',
    'robots' => 'noindex,follow',
];

require __DIR__ . '/includes/header.php';
?>

<section class="page-intro">
    <div class="container narrow">
        <h1>Pagina niet gevonden</h1>
        <p class="lede">Dit adres hoort niet bij een pagina op deze site.</p>
        <p><a class="btn btn-primary" href="/">Naar de homepage</a></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
