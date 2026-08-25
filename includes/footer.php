<footer class="site-footer">
    <div class="container footer-inner">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME) ?> — gebouwd met PHP &amp; MySQL.</p>
        <div class="footer-links">
            <a href="<?= htmlspecialchars(GITHUB_URL) ?>" target="_blank" rel="noopener noreferrer">GitHub</a>
            <?php if (LINKEDIN_URL !== ''): ?>
                <a href="<?= htmlspecialchars(LINKEDIN_URL) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
            <?php endif; ?>
            <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>"><?= htmlspecialchars(SITE_EMAIL) ?></a>
        </div>
    </div>
</footer>
<script src="<?= htmlspecialchars(asset('assets/js/main.js')) ?>" defer></script>
</body>
</html>
