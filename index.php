<?php
require_once __DIR__ . '/includes/db.php';

$projects = get_projects();
$skills   = get_skills_grouped();
$dbOnline = db() !== null;

$flash = $_GET['sent'] ?? null;

require __DIR__ . '/includes/header.php';
?>

<main id="top">

    <section class="hero container">
        <p class="mono accent hero-kicker">Hallo, ik ben</p>
        <h1 class="hero-name"><?= htmlspecialchars(SITE_NAME) ?>.</h1>
        <h2 class="hero-sub">Software Developer.</h2>
        <p class="hero-tagline"><?= htmlspecialchars(SITE_TAGLINE) ?></p>
        <div class="hero-actions">
            <a href="#projects" class="btn btn-primary">Bekijk mijn werk</a>
            <a href="#contact" class="btn">Neem contact op</a>
        </div>
    </section>

    <section id="about" class="container section">
        <h2 class="section-title"><span class="mono accent">01.</span> Over mij</h2>
        <div class="about-grid">
            <div class="about-text">
                <?php foreach (preg_split('/\n\s*\n/', ABOUT_TEXT) as $para): ?>
                    <p><?= nl2br(htmlspecialchars(trim($para))) ?></p>
                <?php endforeach; ?>
                <p class="mono about-meta">
                    <?= htmlspecialchars(SITE_LOCATION) ?> &nbsp;·&nbsp;
                    <a href="<?= htmlspecialchars(GITHUB_URL) ?>" target="_blank" rel="noopener noreferrer">GitHub</a>
                    <?php if (LINKEDIN_URL !== ''): ?>
                        &nbsp;·&nbsp;
                        <a href="<?= htmlspecialchars(LINKEDIN_URL) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                    <?php endif; ?>
                </p>
            </div>
            <aside class="about-sidebar">
                <div class="timeline-section">
                    <h3>Werkervaring</h3>
                    <div class="timeline-item">
                        <p class="timeline-period">2023 — heden</p>
                        <p class="timeline-title">Albert Heijn</p>
                        <p class="timeline-location">Hellevoetsluis</p>
                        <p class="timeline-desc">Werkervaring als vakkenvuller waarbij ik professionaliteit en nauwkeurigheid leer.</p>
                    </div>
                    <div class="timeline-item">
                        <p class="timeline-period">2023 — 2024</p>
                        <p class="timeline-title">Blanche Art Productions</p>
                        <p class="timeline-location">Hellevoetsluis</p>
                        <p class="timeline-desc">Stage bij het kunstbedrijf van mijn moeder waar ik praktische ervaring opdeed in hoe je een grafisch bedrijf opbouwt en beheert.</p>
                    </div>
                    <div class="timeline-item">
                        <p class="timeline-period">2023</p>
                        <p class="timeline-title">Oosterveld Design</p>
                        <p class="timeline-location">Krammer, Brielle</p>
                        <p class="timeline-desc">Stage bij een grafisch bedrijf waar ik ervaring opdeed in design en lay-out, en meerdere projecten heb ondersteund.</p>
                    </div>
                </div>
                <div class="timeline-section">
                    <h3>Educatie</h3>
                    <div class="timeline-item">
                        <p class="timeline-period">2020 — 2024</p>
                        <p class="timeline-title">Helinium</p>
                        <p class="timeline-desc">Economie &amp; Onderneming</p>
                    </div>
                    <div class="timeline-item">
                        <p class="timeline-period">2024 — heden</p>
                        <p class="timeline-title">Grafisch Lyceum Rotterdam</p>
                        <p class="timeline-desc">Software Development — minor Applied Development</p>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section id="projects" class="container section">
        <h2 class="section-title"><span class="mono accent">02.</span> Projecten</h2>
        <p class="section-intro">Een selectie van mijn werk</p>

        <?php if (!$dbOnline): ?>
            <p class="notice">Projecten komen hier te staan zodra de database verbonden is.</p>
        <?php elseif (empty($projects)): ?>
            <p class="notice">Nog geen projecten om te tonen.</p>
        <?php else: ?>
            <div class="project-grid">
                <?php foreach ($projects as $p): ?>
                    <article class="project-card <?= $p['is_featured'] ? 'featured' : '' ?>">
                        <?php if (!empty($p['image_path'])): ?>
                            <div class="project-thumb">
                                <img src="<?= htmlspecialchars($p['image_path']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy" decoding="async">
                            </div>
                        <?php endif; ?>
                        <div class="project-body">
                        <?php if ($p['is_featured']): ?>
                            <span class="badge">Uitgelicht</span>
                        <?php endif; ?>
                        <h3 class="project-title"><?= htmlspecialchars($p['title']) ?></h3>
                        <p class="project-summary"><?= htmlspecialchars($p['summary']) ?></p>
                        <ul class="tech-list">
                            <?php foreach (array_filter(array_map('trim', explode(',', $p['tech_stack']))) as $tech): ?>
                                <li><?= htmlspecialchars($tech) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="project-links">
                            <?php if (!empty($p['repo_url'])): ?>
                                <a href="<?= htmlspecialchars($p['repo_url']) ?>" target="_blank" rel="noopener noreferrer">Code ↗</a>
                            <?php endif; ?>
                            <?php if (!empty($p['live_url'])): ?>
                                <?php $external = str_starts_with($p['live_url'], 'http'); ?>
                                <a href="<?= htmlspecialchars($p['live_url']) ?>"
                                   <?= $external ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                                    <?= $external ? 'Bekijken ↗' : 'Spelen ▶' ?>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($p['download_url'])): ?>
                                <a href="<?= htmlspecialchars($p['download_url']) ?>" download>Download ↓</a>
                            <?php endif; ?>
                        </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section id="skills" class="container section">
        <h2 class="section-title"><span class="mono accent">03.</span> Skills</h2>
        <p class="section-intro">Mijn technische vaardigheden en expertise</p>
        <?php if (empty($skills)): ?>
            <p class="notice">Skills komen hier te staan zodra de database verbonden is.</p>
        <?php else: ?>
            <div class="skills-grid">
                <?php foreach ($skills as $category => $names): ?>
                    <div class="skill-group">
                        <h3><?= htmlspecialchars($category) ?></h3>
                        <ul class="skill-tags">
                            <?php foreach ($names as $name): ?>
                                <li><?= htmlspecialchars($name) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section id="goals" class="container section">
        <h2 class="section-title"><span class="mono accent">04.</span> Doelen</h2>
        <p class="section-intro">Waar ik naartoe werk en waar ik mee bezig ben</p>
        <div class="goals-content">
            <div class="goal-block">
                <h3>Huidige focus</h3>
                <p>Ik ben bezig met het leren maken van websites in PHP en MySQL, en daar wil ik in doorgaan.</p>
            </div>
            <div class="goal-block">
                <h3>Korte termijn</h3>
                <ul class="goals-list">
                    <li>Beter worden in het bouwen van websites</li>
                    <li>Meer complete websites maken</li>
                    <li>Mijn portfolio uitbreiden</li>
                </ul>
            </div>
            <div class="goal-block">
                <h3>Lange termijn</h3>
                <p>Ik wil mijn eigen zzp-onderneming starten en websites maken voor klanten.</p>
            </div>
            <p class="goal-next">Volgende stappen: verder groeien, en daarna als zzp'er websites bouwen voor klanten.</p>
        </div>
    </section>

    <section id="contact" class="container section section-contact">
        <h2 class="section-title"><span class="mono accent">05.</span> Contact</h2>
        <p class="contact-lead">
            Wil je een website, of heb je een vraag? Stuur een bericht.
        </p>

        <?php if ($flash === '1'): ?>
            <p class="flash flash-ok">Bedankt! Je bericht is verstuurd — ik neem zo snel mogelijk contact met je op.</p>
        <?php elseif ($flash === '0'): ?>
            <p class="flash flash-err">Er ging iets mis bij het versturen. Je kunt me ook direct mailen op
                <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>"><?= htmlspecialchars(SITE_EMAIL) ?></a>.</p>
        <?php endif; ?>

        <form action="contact.php" method="post" class="contact-form">
            <div class="form-row">
                <label>
                    Naam
                    <input type="text" name="name" required maxlength="120" autocomplete="name" placeholder="Je naam">
                </label>
                <label>
                    E-mail
                    <input type="email" name="email" required maxlength="190" autocomplete="email" placeholder="naam@bedrijf.nl">
                </label>
            </div>
            <label>
                Bericht
                <textarea name="message" required maxlength="5000" rows="6"
                          placeholder="Hoi Wolf, ik wilde even contact opnemen..."></textarea>
            </label>
            <div class="hp" aria-hidden="true">
                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>
            <button type="submit" class="btn btn-primary">Verstuur bericht</button>
        </form>
    </section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
