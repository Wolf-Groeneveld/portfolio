<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SITE_TITLE) ?></title>
    <meta name="description" content="<?= htmlspecialchars(SITE_TAGLINE) ?>">
    <meta name="theme-color" content="#000000">
    <meta name="robots" content="index,follow">
    <meta property="og:title" content="<?= htmlspecialchars(SITE_TITLE) ?>">
    <meta property="og:description" content="<?= htmlspecialchars(SITE_TAGLINE) ?>">
    <meta property="og:type" content="website">
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars(asset('assets/img/favicon.svg')) ?>">
    <link rel="shortcut icon" href="<?= htmlspecialchars(asset('assets/img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('assets/css/style.css')) ?>">
    <script>
        (function () {
            var nav = performance.getEntriesByType('navigation')[0];
            if (!nav || nav.type !== 'reload') return;
            history.scrollRestoration = 'manual';
            if (location.hash) {
                history.replaceState(null, '', location.pathname + location.search);
            }
        })();
    </script>
</head>
<body>
<canvas id="bg-canvas" aria-hidden="true"></canvas>
<header class="site-header">
    <nav class="container nav">
        <a href="#top" class="logo">&lt;<?= htmlspecialchars(SITE_NAME) ?> /&gt;</a>
        <button type="button" class="nav-toggle" aria-label="Menu openen" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links">
            <li><a href="#about">Over mij</a></li>
            <li><a href="#projects">Projecten</a></li>
            <li><a href="#skills">Skills</a></li>
            <li><a href="#goals">Doelen</a></li>
            <li><a href="#contact">Contact</a></li>
            <?php if (is_file(dirname(__DIR__) . '/' . RESUME_PATH)): ?>
                <li><a href="<?= htmlspecialchars(RESUME_PATH) ?>" class="btn btn-small" download="CV-Wolf-Groeneveld.png">CV</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
