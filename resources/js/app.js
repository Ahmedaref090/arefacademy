// Aref Academy – JS entry point (bundled by Vite).
// Alpine.js is loaded via CDN in the layouts; this file holds global helpers.

// Reveal-on-scroll: elements marked with [data-reveal] fade/slide in
// the first time they enter the viewport.
const revealObserver = new IntersectionObserver(
    (entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        }
    },
    { threshold: 0.15 },
);

document.querySelectorAll('[data-reveal]').forEach((el) => revealObserver.observe(el));
