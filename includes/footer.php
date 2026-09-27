</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <p class="footer-brand">Toos Boerlage</p>
            <p class="footer-note">Originele schilderijen uit Hellevoetsluis.</p>
        </div>
        <nav aria-label="Footer">
            <ul>
                <li><a href="/kunstwerken">Collectie</a></li>
                <li><a href="/over">Over</a></li>
                <li><a href="/opdrachten">Opdrachten</a></li>
                <li><a href="/contact">Contact</a></li>
            </ul>
        </nav>
        <div class="footer-contact">
            <p><a href="mailto:<?= e(ARTIST_EMAIL) ?>"><?= e(ARTIST_EMAIL) ?></a></p>
            <p><a href="tel:<?= e(ARTIST_PHONE_TEL) ?>"><?= e(ARTIST_PHONE_DISPLAY) ?></a></p>
            <ul class="footer-sites">
                <?php foreach (ARTIST_WEBSITES as $site): ?>
                    <li><a href="<?= e($site['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($site['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="container footer-base">
        <p>&copy; <?= date('Y') ?> Toos Boerlage</p>
    </div>
</footer>

<div class="cart-overlay" data-cart-overlay hidden></div>
<aside id="cart-drawer" class="cart-drawer" role="dialog" aria-modal="true" aria-labelledby="cart-title" hidden>
    <div class="cart-drawer-head">
        <h2 id="cart-title">Winkelwagen</h2>
        <button type="button" class="icon-button" data-cart-close>Sluiten</button>
    </div>
    <p class="cart-sync" data-cart-notice hidden></p>
    <div class="cart-drawer-body" data-cart-body></div>
    <div class="cart-drawer-foot" data-cart-foot hidden>
        <p class="subtotal"><span>Subtotaal</span> <strong data-cart-subtotal></strong></p>
        <p class="cart-shipping">Verzendkosten zijn nog niet vastgesteld en zitten niet in dit bedrag.</p>
        <a class="btn btn-primary" href="/afrekenen">Verder naar afrekenen</a>
    </div>
</aside>

<dialog id="lightbox" class="lightbox" aria-labelledby="lightbox-title">
    <div class="lightbox-bar">
        <p id="lightbox-title">Vergrote afbeelding</p>
        <button type="button" class="icon-button" data-lightbox-close>Sluiten</button>
    </div>
    <div class="lightbox-stage">
        <button type="button" class="icon-button" data-lightbox-prev>Vorige</button>
        <figure>
            <img alt="" data-lightbox-img>
            <figcaption data-lightbox-caption></figcaption>
        </figure>
        <button type="button" class="icon-button" data-lightbox-next>Volgende</button>
    </div>
</dialog>

<script type="application/json" id="catalog-index"><?= json_encode(public_catalog_index(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="<?= e(asset('assets/js/main.js')) ?>" defer></script>
</body>
</html>
