<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sylensial's Nightmare — spelen | <?= htmlspecialchars(SITE_NAME) ?></title>
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars(asset('assets/img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('assets/css/style.css')) ?>">
    <style>
        html, body { height: 100%; overflow: hidden; }
        .play-page {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .play-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 24px;
            border-bottom: 1px solid var(--border-soft);
            background: rgba(0, 0, 0, 0.78);
            backdrop-filter: blur(12px);
        }
        .play-bar a { font-weight: 500; font-size: 0.9rem; }
        .play-bar h1 { font-size: 1rem; color: var(--heading); font-weight: 600; }
        .play-hints {
            font-family: var(--font-mono);
            font-size: 0.72rem;
            color: var(--text-dim);
        }
        .play-frame { flex: 1; border: none; width: 100%; background: #000; }
        @media (max-width: 720px) {
            .play-hints { display: none; }
        }
    </style>
</head>
<body>
<div class="play-page">
    <div class="play-bar">
        <a href="index.php#projects">&larr; Terug naar portfolio</a>
        <h1>Sylensial's Nightmare</h1>
        <p class="play-hints">Klik/Enter = start &nbsp;·&nbsp; Spatie = wave starten &nbsp;·&nbsp; G = gokken &nbsp;·&nbsp; Esc/rechtsklik = annuleren</p>
    </div>
    <?php $gameVersion = @filemtime(__DIR__ . '/play/index.html') ?: time(); ?>
    <iframe class="play-frame" src="play/index.html?v=<?= $gameVersion ?>" allow="autoplay; fullscreen" title="Sylensial's Nightmare"></iframe>
</div>
</body>
</html>
