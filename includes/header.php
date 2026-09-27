<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$pageTitle = $page['title'] ?? SITE_TITLE;
$pageDescription = $page['description'] ?? SITE_DESCRIPTION;
$pageRobots = $page['robots'] ?? 'index,follow';
$pagePath = $page['path'] ?? '/';
$ogType = $page['ogType'] ?? 'website';
$canonical = site_url(ltrim($pagePath, '/'));
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="robots" content="<?= e($pageRobots) ?>">
    <meta name="theme-color" content="#FFF9F0">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta property="og:locale" content="nl_NL">
    <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e(site_url(asset('assets/img/og.png'))) ?>">
    <meta property="og:image:alt" content="Toos Boerlage, atelier voor originele schilderijen">
    <?php if (!empty($page['productPrice'])): ?>
        <meta property="product:price:amount" content="<?= e((string) $page['productPrice']) ?>">
        <meta property="product:price:currency" content="EUR">
    <?php endif; ?>
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('assets/img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
    <script>document.documentElement.classList.add('js');</script>
</head>
<body>
<a class="skip-link" href="#inhoud">Ga naar inhoud</a>
<header class="site-header">
    <div class="container header-bar">
        <a class="brand" href="/">Toos Boerlage</a>
        <form class="header-search" role="search" action="/kunstwerken" method="get">
            <label class="sr-only" for="header-q">Zoek kunstwerken</label>
            <input id="header-q" type="search" name="q" placeholder="Titel of omschrijving" value="<?= e((string) ($_GET['q'] ?? '')) ?>" maxlength="120">
            <button type="submit">Zoek</button>
        </form>
        <a class="cart-link" id="cart-toggle" href="/afrekenen" aria-controls="cart-drawer" aria-expanded="false">
            <span class="cart-label">Winkelwagen</span>
            <span class="cart-count" data-cart-count>0</span>
        </a>
        <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="site-nav">
            <span class="nav-toggle-bars" aria-hidden="true"></span>
            <span class="nav-toggle-label">Menu</span>
        </button>
        <nav class="site-nav" id="site-nav" aria-label="Hoofdmenu">
            <ul class="nav-links">
                <li><a href="/kunstwerken"<?= nav_current('/kunstwerken') ?>>Collectie</a></li>
                <li><a href="/over"<?= nav_current('/over') ?>>Over</a></li>
                <li><a href="/opdrachten"<?= nav_current('/opdrachten') ?>>Opdrachten</a></li>
                <li><a href="/contact"<?= nav_current('/contact') ?>>Contact</a></li>
            </ul>
        </nav>
    </div>
</header>
<main id="inhoud">
