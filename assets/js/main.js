(function () {
    const catalogNode = document.getElementById('catalog-index');
    const catalog = catalogNode ? JSON.parse(catalogNode.textContent || '[]') : [];
    const byId = new Map(catalog.map((item) => [item.id, item]));
    const storageKey = 'toos-boerlage-winkelwagen';

    const money = new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' });

    function formatPrice(value) {
        return money.format(value);
    }

    function readStored() {
        const raw = localStorage.getItem(storageKey);
        if (!raw) {
            return { entries: [], corrupt: false };
        }
        try {
            const parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) {
                return { entries: [], corrupt: true };
            }
            return { entries: parsed, corrupt: false };
        } catch (error) {
            return { entries: [], corrupt: true };
        }
    }

    function normalize(raw) {
        const seen = new Set();
        const items = [];
        let dropped = 0;
        raw.forEach((entry) => {
            const id = entry && typeof entry.id === 'string' ? entry.id : '';
            const product = byId.get(id);
            if (!product || seen.has(id) || product.status !== 'available' || typeof product.price !== 'number') {
                dropped += 1;
                return;
            }
            seen.add(id);
            items.push({ id: product.id });
        });
        return { items, dropped };
    }

    let removedNotice = false;
    const stored = readStored();
    const initial = normalize(stored.entries);
    if (stored.corrupt || initial.dropped > 0) {
        removedNotice = true;
    }
    localStorage.setItem(storageKey, JSON.stringify(initial.items));

    function cartItems() {
        return normalize(readStored().entries).items.map((entry) => byId.get(entry.id)).filter(Boolean);
    }

    function save(items) {
        localStorage.setItem(storageKey, JSON.stringify(items.map((item) => ({ id: item.id }))));
        renderCart();
        renderCheckout();
        syncAddButtons();
    }

    const countNodes = document.querySelectorAll('[data-cart-count]');
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.querySelector('[data-cart-overlay]');
    const body = document.querySelector('[data-cart-body]');
    const foot = document.querySelector('[data-cart-foot]');
    const subtotalNode = document.querySelector('[data-cart-subtotal]');
    const notice = document.querySelector('[data-cart-notice]');
    const toggle = document.getElementById('cart-toggle');
    let lastFocus = null;

    function renderCart() {
        const items = cartItems();
        countNodes.forEach((node) => {
            node.textContent = String(items.length);
        });
        if (!body || !foot || !subtotalNode) {
            return;
        }
        if (removedNotice && notice) {
            notice.hidden = false;
            notice.textContent = 'Een of meer werken zijn uit de winkelwagen gehaald omdat ze niet meer beschikbaar zijn of niet meer in de collectie staan.';
        }
        if (items.length === 0) {
            body.innerHTML = '<div class="empty-state"><h3>Je winkelwagen is leeg</h3><p>Unieke werken blijven hier bewaard in deze browser.</p><p><a class="btn btn-secondary" href="/kunstwerken">Bekijk de collectie</a></p></div>';
            foot.hidden = true;
            return;
        }
        foot.hidden = false;
        const total = items.reduce((sum, item) => sum + item.price, 0);
        subtotalNode.textContent = formatPrice(total);
        body.innerHTML = items.map((item) => {
            const href = '/kunstwerken/' + encodeURIComponent(item.slug);
            return '<article class="cart-item">'
                + '<img src="/assets/img/placeholder.svg" alt="Tijdelijke plaatsaanduiding. Het werk ' + escapeHtml(item.title) + ' is hier niet afgebeeld." width="72" height="90">'
                + '<div><h3><a href="' + href + '">' + escapeHtml(item.title) + '</a></h3>'
                + '<p>Aantal: 1</p>'
                + '<p>' + escapeHtml(formatPrice(item.price)) + '</p>'
                + '<button type="button" class="text-button" data-remove-id="' + escapeHtml(item.id) + '">Verwijder</button>'
                + '</div></article>';
        }).join('');
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;');
    }

    function openCart() {
        if (!drawer || !overlay || !toggle) return;
        lastFocus = document.activeElement;
        drawer.hidden = false;
        overlay.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        const close = drawer.querySelector('[data-cart-close]');
        if (close) close.focus();
    }

    function closeCart() {
        if (!drawer || !overlay || !toggle) return;
        drawer.hidden = true;
        overlay.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        if (lastFocus && typeof lastFocus.focus === 'function') {
            lastFocus.focus();
        }
    }

    if (toggle && drawer) {
        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            if (drawer.hidden) openCart();
            else closeCart();
        });
    }
    document.querySelectorAll('[data-cart-close]').forEach((button) => {
        button.addEventListener('click', closeCart);
    });
    if (overlay) overlay.addEventListener('click', closeCart);

    document.addEventListener('keydown', (event) => {
        if (!drawer || drawer.hidden) {
            return;
        }
        if (event.key === 'Escape') {
            closeCart();
            return;
        }
        if (event.key !== 'Tab') {
            return;
        }
        const focusable = [...drawer.querySelectorAll('a[href], button, input, select, textarea')]
            .filter((el) => !el.disabled && el.getClientRects().length > 0);
        if (focusable.length === 0) {
            return;
        }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    if (body) {
        body.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-id]');
            if (!button) return;
            const id = button.getAttribute('data-remove-id');
            save(cartItems().filter((item) => item.id !== id));
        });
    }

    function syncAddButtons() {
        const ids = new Set(cartItems().map((item) => item.id));
        document.querySelectorAll('[data-add-to-cart]').forEach((button) => {
            const id = button.getAttribute('data-id');
            const inCart = ids.has(id);
            button.disabled = inCart;
            button.textContent = inCart ? 'Staat in de winkelwagen' : 'In winkelwagen';
        });
    }

    document.querySelectorAll('[data-add-to-cart]').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const product = byId.get(id);
            const feedback = document.querySelector('[data-cart-feedback]');
            if (!product || product.status !== 'available' || typeof product.price !== 'number') {
                if (feedback) {
                    feedback.hidden = false;
                    feedback.textContent = 'Dit werk kan niet in de winkelwagen.';
                }
                return;
            }
            const items = cartItems();
            if (!items.some((item) => item.id === id)) {
                items.push(product);
                save(items);
            }
            if (feedback) {
                feedback.hidden = false;
                feedback.textContent = 'Toegevoegd. Een origineel kan maar één keer in de winkelwagen staan.';
            }
            openCart();
        });
    });

    function renderCheckout() {
        const target = document.querySelector('[data-checkout-summary]');
        if (!target) return;
        const items = cartItems();
        if (items.length === 0) {
            target.innerHTML = '<p class="empty-copy">Je winkelwagen is leeg. <a href="/kunstwerken">Bekijk de collectie</a>.</p>';
            return;
        }
        const total = items.reduce((sum, item) => sum + item.price, 0);
        target.innerHTML = '<ul class="summary-list">' + items.map((item) => {
            return '<li><a href="/kunstwerken/' + encodeURIComponent(item.slug) + '">' + escapeHtml(item.title) + '</a><span>' + escapeHtml(formatPrice(item.price)) + '</span></li>';
        }).join('') + '</ul><p class="subtotal"><span>Subtotaal</span> <strong>' + escapeHtml(formatPrice(total)) + '</strong></p>';
    }

    const filterForm = document.querySelector('.filter-bar');
    if (filterForm) {
        filterForm.addEventListener('submit', () => {
            filterForm.querySelectorAll('input, select').forEach((field) => {
                const empty = field.value.trim() === '';
                const defaultSort = field.name === 'sorteer' && field.value === 'titel-op';
                if (empty || defaultSort) {
                    field.disabled = true;
                }
            });
        });
    }

    const navToggle = document.querySelector('.nav-toggle');
    const nav = document.getElementById('site-nav');
    if (navToggle && nav) {
        navToggle.addEventListener('click', () => {
            const open = nav.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            navToggle.querySelector('.nav-toggle-label').textContent = open ? 'Sluiten' : 'Menu';
        });
        nav.addEventListener('click', (event) => {
            if (event.target.closest('a')) {
                nav.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.querySelector('.nav-toggle-label').textContent = 'Menu';
            }
        });
    }

    const messages = {
        voornaam: 'Vul je voornaam in.',
        achternaam: 'Vul je achternaam in.',
        naam: 'Vul je naam in.',
        email: 'Vul een geldig e-mailadres in.',
        telefoon: 'Vul een geldig telefoonnummer in, of laat het veld leeg.',
        straat: 'Vul de straat in.',
        huisnummer: 'Vul het huisnummer in.',
        postcode: 'Vul de postcode in.',
        plaats: 'Vul de plaats in.',
        land: 'Vul het land in.',
        onderwerp: 'Vul een onderwerp in.',
        bericht: 'Schrijf een bericht.',
        afmetingen: 'Vul de gewenste afmetingen in.',
        kleuren: 'Vul de gewenste kleuren in.',
        budget: 'Vul een budget in.',
        beschrijving: 'Beschrijf de opdracht.',
    };

    function showError(input, message) {
        const field = input.closest('.field');
        if (!field) return;
        field.classList.add('is-invalid');
        input.setAttribute('aria-invalid', 'true');
        let error = field.querySelector('.field-error');
        if (!error) {
            error = document.createElement('p');
            error.className = 'field-error';
            error.id = input.id + '-error';
            field.appendChild(error);
        }
        error.textContent = message;
        const described = new Set((input.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
        described.add(error.id);
        input.setAttribute('aria-describedby', Array.from(described).join(' '));
    }

    function clearErrors(form) {
        form.querySelectorAll('.field').forEach((field) => field.classList.remove('is-invalid'));
        form.querySelectorAll('.field-error').forEach((node) => node.remove());
        form.querySelectorAll('[aria-invalid]').forEach((node) => node.removeAttribute('aria-invalid'));
    }

    function isNetherlands(value) {
        return ['nederland', 'nl', 'the netherlands', 'netherlands', 'holland'].includes(value.trim().toLowerCase());
    }

    function validateForm(form) {
        clearErrors(form);
        let first = null;
        form.querySelectorAll('input, textarea, select').forEach((input) => {
            if (input.type === 'hidden' || input.type === 'file' || input.name === 'bedrijf') return;
            const value = input.value.trim();
            const required = input.dataset.required === 'true';
            let message = '';
            if (required && value === '') {
                message = messages[input.name] || 'Vul dit veld in.';
            } else if (input.type === 'email' && value !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                message = messages.email;
            } else if (input.name === 'telefoon' && value !== '') {
                const digits = value.replace(/\D/g, '');
                if (!/^[0-9+\s()./-]{8,40}$/.test(value) || digits.length < 8 || digits.length > 15) {
                    message = messages.telefoon;
                }
            } else if (input.name === 'postcode' && value !== '') {
                const country = form.querySelector('[name="land"]');
                const countryValue = country ? country.value : 'Nederland';
                if (isNetherlands(countryValue) && !/^[1-9][0-9]{3}\s?[A-Za-z]{2}$/.test(value)) {
                    message = 'Gebruik een Nederlandse postcode, bijvoorbeeld 3221 KA.';
                }
            } else if (input.maxLength > 0 && value.length > input.maxLength) {
                message = 'Dit veld is te lang.';
            }
            if (message) {
                showError(input, message);
                if (!first) first = input;
            }
        });
        return first;
    }

    const checkout = document.getElementById('checkout-form');
    if (checkout) {
        checkout.addEventListener('submit', (event) => {
            const payload = document.getElementById('cart-payload');
            const items = cartItems();
            if (payload) {
                payload.value = JSON.stringify(items.map((item) => ({ id: item.id })));
            }
            const first = validateForm(checkout);
            let cartError = document.getElementById('cart-error');
            if (items.length === 0) {
                event.preventDefault();
                if (!cartError) {
                    cartError = document.createElement('p');
                    cartError.id = 'cart-error';
                    cartError.className = 'field-error';
                    checkout.prepend(cartError);
                }
                cartError.textContent = 'Je winkelwagen is leeg. Voeg eerst een beschikbaar werk toe.';
                cartError.focus?.();
                return;
            }
            if (cartError) cartError.remove();
            if (first) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    ['contact-form', 'request-form'].forEach((id) => {
        const form = document.getElementById(id);
        if (!form) return;
        form.addEventListener('submit', (event) => {
            const file = form.querySelector('input[type="file"]');
            if (file && file.files && file.files[0]) {
                const max = Number(file.dataset.maxBytes || 0);
                const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                if ((max && file.files[0].size > max) || (file.files[0].type && !allowed.includes(file.files[0].type))) {
                    event.preventDefault();
                    showError(file, file.files[0].size > max
                        ? 'Het bestand is te groot.'
                        : 'Kies een JPEG-, PNG-, WEBP- of GIF-bestand.');
                    file.focus();
                    return;
                }
            }
            const first = validateForm(form);
            if (first) {
                event.preventDefault();
                first.focus();
            }
        });
    });

    const fileInput = document.getElementById('afbeelding');
    const chosen = document.querySelector('[data-file-chosen]');
    const fileName = document.querySelector('[data-file-name]');
    const fileClear = document.querySelector('[data-file-clear]');
    if (fileInput && chosen && fileName) {
        fileInput.addEventListener('change', () => {
            const file = fileInput.files && fileInput.files[0];
            if (!file) {
                chosen.hidden = true;
                fileName.textContent = '';
                return;
            }
            chosen.hidden = false;
            fileName.textContent = file.name;
        });
    }
    if (fileClear && fileInput) {
        fileClear.addEventListener('click', () => {
            fileInput.value = '';
            if (chosen) chosen.hidden = true;
            if (fileName) fileName.textContent = '';
            fileInput.focus();
        });
    }

    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.querySelector('[data-lightbox-img]');
    const lightboxCaption = document.querySelector('[data-lightbox-caption]');
    let galleryImages = [];
    let galleryIndex = 0;

    function showSlide(index) {
        if (!galleryImages.length || !lightboxImg) return;
        galleryIndex = (index + galleryImages.length) % galleryImages.length;
        const image = galleryImages[galleryIndex];
        lightboxImg.src = image.src;
        lightboxImg.alt = image.alt;
        if (lightboxCaption) lightboxCaption.textContent = image.alt;
        document.querySelectorAll('[data-gallery] [data-gallery-open]').forEach((button) => {
            const buttonIndex = Number(button.dataset.index);
            if (button.closest('.thumbs')) {
                if (buttonIndex === galleryIndex) button.setAttribute('aria-current', 'true');
                else button.removeAttribute('aria-current');
            }
        });
    }

    document.querySelectorAll('[data-gallery]').forEach((gallery) => {
        const data = gallery.querySelector('.gallery-data');
        if (!data) return;
        gallery.addEventListener('click', (event) => {
            const button = event.target.closest('[data-gallery-open]');
            if (!button || !lightbox || typeof lightbox.showModal !== 'function') return;
            galleryImages = JSON.parse(data.textContent || '[]');
            showSlide(Number(button.dataset.index || 0));
            lightbox.showModal();
        });
    });

    if (lightbox) {
        lightbox.querySelector('[data-lightbox-close]')?.addEventListener('click', () => lightbox.close());
        lightbox.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => showSlide(galleryIndex - 1));
        lightbox.querySelector('[data-lightbox-next]')?.addEventListener('click', () => showSlide(galleryIndex + 1));
        lightbox.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') showSlide(galleryIndex - 1);
            if (event.key === 'ArrowRight') showSlide(galleryIndex + 1);
        });
        lightbox.addEventListener('close', () => {
            galleryImages = [];
        });
    }

    renderCart();
    renderCheckout();
    syncAddButtons();
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('.filter-bar [disabled]').forEach((field) => {
            field.disabled = false;
        });
        renderCart();
        renderCheckout();
        syncAddButtons();
    });
})();
