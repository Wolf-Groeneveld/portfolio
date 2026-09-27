<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$page = [
    'title' => 'Over | Toos Boerlage',
    'description' => 'Over kunstenares Toos Boerlage uit Hellevoetsluis: opleiding, werkwijze, technieken en werk in opdracht.',
    'path' => '/over',
];

require __DIR__ . '/includes/header.php';
?>

<section class="page-intro">
    <div class="container narrow">
        <h1>Over Toos</h1>
        <p class="lede"><?= e(bio_excerpt()) ?></p>
    </div>
</section>

<section class="section section-tight">
    <div class="container prose-page">
        <?php foreach (bio_paragraphs() as $paragraph): ?>
            <p><?= e($paragraph) ?></p>
        <?php endforeach; ?>
        <p class="field-hint">Een portret- of atelierfoto staat hier nog niet. Er is geen bruikbaar beeld toegevoegd.</p>
        <p><a class="btn btn-secondary" href="/kunstwerken">Bekijk de collectie</a></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
