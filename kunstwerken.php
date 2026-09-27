<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$result = filter_artworks([
    'q' => (string) ($_GET['q'] ?? ''),
    'categorie' => (string) ($_GET['categorie'] ?? ''),
    'techniek' => (string) ($_GET['techniek'] ?? ''),
    'beschikbaarheid' => (string) ($_GET['beschikbaarheid'] ?? ''),
    'sorteer' => (string) ($_GET['sorteer'] ?? ''),
]);
$items = $result['items'];
$state = $result['state'];
$count = count($items);
$countLabel = $count === 1 ? '1 werk' : $count . ' werken';

$page = [
    'title' => 'Collectie | Toos Boerlage',
    'description' => 'Bekijk de collectie originele schilderijen van Toos Boerlage. Filter op categorie, techniek en beschikbaarheid.',
    'path' => '/kunstwerken',
];

require __DIR__ . '/includes/header.php';
?>

<section class="page-intro">
    <div class="container narrow">
        <h1>Collectie</h1>
        <p class="lede">Schilderijen en opdrachten van Toos Boerlage, met titel, jaar, techniek, materiaal, afmetingen en prijs zoals die eerder zijn vastgelegd. De afbeeldingen volgen nog.</p>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <?php filter_bar($state); ?>
        <p class="result-count"><?= e($countLabel) ?></p>
        <?php if ($items === []): ?>
            <div class="empty-state">
                <h2>Geen werken gevonden</h2>
                <p>Pas de filters of de zoekterm aan, of bekijk de hele collectie.</p>
                <p><a class="btn btn-secondary" href="/kunstwerken">Wis filters</a></p>
            </div>
        <?php else: ?>
            <?php artwork_grid($items); ?>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
