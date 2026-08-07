{{--
    Global toast stack mount. Include once at the end of every layout.
    Injects session flashes (status / error) as animated toasts via app.js.
--}}
<div id="toast-stack" aria-live="polite" x-cloak></div>

<script>
    window.__flashes = window.__flashes || {};
    window.__flashes.status = @json(session('status'));
    window.__flashes.error = @json(session('error'));
    window.__flashes.errors = @json(session('errors') ? session('errors')->all() : []);
</script>
