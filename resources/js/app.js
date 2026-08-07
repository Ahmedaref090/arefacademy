// Aref Academy – JS entry point (bundled by Vite).
// Alpine.js is loaded via CDN in the layouts; this file holds global helpers.

/* ------------------------------------------------------------------
   Toast system — animated success / error / warning / info toasts.
   Usage: ArefToast.success('Title', 'Message')
   ------------------------------------------------------------------ */
(function () {
    const ICONS = {
        success: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        error: '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
        warning: '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        info: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
    };
    const TITLES = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Info' };

    function stack() {
        let el = document.getElementById('toast-stack');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toast-stack';
            el.setAttribute('aria-live', 'polite');
            document.body.appendChild(el);
        }
        return el;
    }

    function iconSvg(type) {
        return `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">${ICONS[type] || ICONS.info}</svg>`;
    }

    function notify(type, title, message, duration = 4500) {
        const el = document.createElement('div');
        el.className = `toast toast-${type}`;
        el.innerHTML = `
            <span class="toast-ico">${iconSvg(type)}</span>
            <div class="toast-body">
                <div class="toast-title">${title || TITLES[type]}</div>
                <div class="toast-msg">${message || ''}</div>
            </div>
            <button type="button" class="toast-close" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="h-4 w-4"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>`;
        el.querySelector('.toast-close').addEventListener('click', () => dismiss(el));

        stack().appendChild(el);
        if (duration > 0) setTimeout(() => dismiss(el), duration);
    }

    function dismiss(el) {
        if (!el) return;
        el.classList.add('is-out');
        setTimeout(() => el.remove(), 350);
    }

    window.ArefToast = {
        success: (m, t) => notify('success', t || TITLES.success, m),
        error: (m, t) => notify('error', t || TITLES.error, m),
        warning: (m, t) => notify('warning', t || TITLES.warning, m),
        info: (m, t) => notify('info', t || TITLES.info, m),
    };

    // Fire queued flash messages (injected by <x-toasts/>).
    function fireFlashes() {
        const f = window.__flashes || {};
        if (f.status) ArefToast.success(f.status);
        if (f.error) ArefToast.error(f.error);
        if (Array.isArray(f.errors) && f.errors.length) {
            f.errors.slice(0, 4).forEach((e) => ArefToast.error(e));
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fireFlashes);
    } else {
        fireFlashes();
    }
})();

/* ------------------------------------------------------------------
   Password visibility toggle — any <button data-password-toggle> with
   data-target="<input-id>" flips its sibling password field between
   password/text and swaps the eye / eye-slash icons.
   ------------------------------------------------------------------ */
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-password-toggle]');
    if (!btn) return;

    const target = document.getElementById(btn.dataset.target);
    if (!target) return;

    const isAr = (document.documentElement.lang || 'ar').toLowerCase().startsWith('ar');
    const show = target.type === 'password';
    target.type = show ? 'text' : 'password';

    const icoShow = btn.querySelector('[data-ico-show]');
    const icoHide = btn.querySelector('[data-ico-hide]');
    if (icoShow) icoShow.classList.toggle('hidden', show);
    if (icoHide) icoHide.classList.toggle('hidden', !show);

    btn.setAttribute('aria-label', show
        ? (isAr ? 'إخفاء كلمة المرور' : 'Hide password')
        : (isAr ? 'إظهار كلمة المرور' : 'Show password'));
    target.focus();
});

/* ------------------------------------------------------------------
   Strict phone validation — digits-only, exactly 11 digits (Egyptian
   mobile format). Applied to any input carrying [data-phone]. A local
   flag keeps the input numeric-only, and both live and on-submit checks
   surface a localized warning toast and block submission on failure.
   ------------------------------------------------------------------ */
(function () {
    const PHONE_PATTERN = /^01[0125][0-9]{8}$/;
    const AR = (document.documentElement.lang || 'ar').toLowerCase().startsWith('ar');
    const MSG = AR
        ? 'رقم الهاتف غير صالح. يجب أن يتكون من 11 رقماً ويحتوي على أرقام فقط.'
        : 'Invalid phone number. It must be strictly 11 digits and contain numbers only.';

    function markInvalid(input) {
        input.classList.add('is-invalid');
    }
    function clearInvalid(input) {
        input.classList.remove('is-invalid');
    }
    function digitsOnly(value) {
        return String(value).replace(/[^0-9]/g, '');
    }
    function valid(value) {
        return PHONE_PATTERN.test(value);
    }

    document.addEventListener('input', function (e) {
        const el = e.target;
        if (!(el instanceof HTMLInputElement) || !el.hasAttribute('data-phone')) return;

        // Digits only — reject letters, spaces and special characters live.
        const cleaned = digitsOnly(el.value).slice(0, 11);
        if (el.value !== cleaned) el.value = cleaned;

        if (el.value !== '' && !valid(el.value)) markInvalid(el); else clearInvalid(el);
    }, true);

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || !form.matches('form')) return;

        const phones = Array.from(form.querySelectorAll('input[data-phone]')).filter(
            (el) => el.value.trim() !== ''
        );

        for (const el of phones) {
            if (!valid(digitsOnly(el.value))) {
                e.preventDefault();
                e.stopImmediatePropagation();
                warnInvalid(el);
                el.focus();
                return;
            }
        }
    }, true);

    function warnInvalid(el) {
        if (window.ArefToast) window.ArefToast.warning(MSG);
        markInvalid(el);
    }
})();

/* ------------------------------------------------------------------
   Reveal-on-scroll — elements marked with [data-reveal] fade/slide in
   with a subtle stagger, respecting prefers-reduced-motion.
   ------------------------------------------------------------------ */
(function () {
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced || !('IntersectionObserver' in window)) {
        document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const revealObserver = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            }
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );

    document.querySelectorAll('[data-reveal]').forEach((el) => revealObserver.observe(el));
})();

/* ------------------------------------------------------------------
   Tiny global niceties: ripple-less pressed feedback is CSS-only;
   here we add a "go-top" floating button after long pages.
   ------------------------------------------------------------------ */
(function () {
    const btn = document.createElement('button');
    btn.setAttribute('aria-label', 'Top');
    btn.id = 'go-top';
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><polyline points="18 15 12 9 6 15"/></svg>';
    Object.assign(btn.style, {
        position: 'fixed', bottom: '1.25rem', insetInlineStart: '1.25rem', zIndex: '90',
        width: '2.75rem', height: '2.75rem', borderRadius: '999px', cursor: 'pointer',
        border: 'none', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center',
        background: 'linear-gradient(135deg,#6d38f6,#d946ef)',
        boxShadow: '0 10px 25px -5px rgba(109,56,246,.5)',
        opacity: '0', visibility: 'hidden', transform: 'translateY(8px)',
        transition: 'opacity .3s ease, transform .3s ease, visibility .3s ease',
    });
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    document.body.appendChild(btn);

    const toggle = () => {
        const show = window.scrollY > 600;
        btn.style.opacity = show ? '1' : '0';
        btn.style.visibility = show ? 'visible' : 'hidden';
        btn.style.transform = show ? 'translateY(0)' : 'translateY(8px)';
    };
    window.addEventListener('scroll', toggle, { passive: true });
    toggle();
})();