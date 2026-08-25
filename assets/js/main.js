const SCROLL_KEY = 'portfolio-scroll';
const isReload = performance.getEntriesByType('navigation')[0]?.type === 'reload';

function restoreScroll() {
    const raw = sessionStorage.getItem(SCROLL_KEY);
    if (raw === null) return;
    const y = Number(raw);
    if (!Number.isFinite(y)) return;
    document.documentElement.style.scrollBehavior = 'auto';
    window.scrollTo(0, y);
    document.documentElement.style.scrollBehavior = '';
}

if (isReload) {
    restoreScroll();
    window.addEventListener('load', restoreScroll);
}

window.addEventListener('scroll', () => {
    sessionStorage.setItem(SCROLL_KEY, String(window.scrollY));
}, { passive: true });

const toggle = document.querySelector('.nav-toggle');
const links = document.querySelector('.nav-links');

if (toggle && links) {
    toggle.addEventListener('click', () => {
        const open = links.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(open));
    });

    links.addEventListener('click', (e) => {
        if (e.target.tagName === 'A') {
            links.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
}

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
if (!reduceMotion) {
    const revealTargets = document.querySelectorAll('.section, .project-card, .skill-group, .goal-block');
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12 }
    );

    revealTargets.forEach((el) => {
        el.classList.add('reveal');
        const rect = el.getBoundingClientRect();
        if (rect.top < innerHeight && rect.bottom > 0) {
            el.classList.add('visible');
        } else {
            observer.observe(el);
        }
    });
}

const canvas = document.getElementById('bg-canvas');
const ctx = canvas?.getContext('2d');

if (canvas && ctx && !reduceMotion) {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const particles = [];
    const particleCount = window.innerWidth < 720 ? 60 : 150;
    const useGlow = window.innerWidth >= 720;

    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            radius: Math.random() * 2 + 0.5,
            speedY: Math.random() * 0.5 + 0.2,
            speedX: Math.random() * 0.4 - 0.2,
            opacity: Math.random() * 0.3 + 0.1
        });
    }

    let running = false;

    function animate() {
        if (document.hidden) {
            running = false;
            return;
        }

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        particles.forEach((p) => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255, 255, 255, ${p.opacity})`;
            if (useGlow) {
                ctx.shadowBlur = 10;
                ctx.shadowColor = `rgba(255, 255, 255, ${p.opacity * 0.6})`;
            }
            ctx.fill();
            if (useGlow) ctx.shadowBlur = 0;

            p.y += p.speedY;
            p.x += p.speedX;

            if (p.y > canvas.height) {
                p.y = -5;
                p.x = Math.random() * canvas.width;
            }
            if (p.x > canvas.width) p.x = 0;
            if (p.x < 0) p.x = canvas.width;
        });

        requestAnimationFrame(animate);
    }

    function start() {
        if (running) return;
        running = true;
        requestAnimationFrame(animate);
    }

    start();
    document.addEventListener('visibilitychange', start);

    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        particles.forEach((p) => {
            p.x = Math.min(p.x, canvas.width);
            p.y = Math.min(p.y, canvas.height);
        });
    });
}
