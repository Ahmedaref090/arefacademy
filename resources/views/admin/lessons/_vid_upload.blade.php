@php
    $hasR2 = config('filesystems.disks.r2.bucket') !== '';
@endphp
<div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-900/40">
    <label class="label">{{ __('Upload Video to Cloudflare (Direct)') }}</label>

    @if(! $hasR2)
        <p class="mb-2 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-400">
            {{ __('Cloudflare R2 is not configured yet. Set the CLOUDFLARE_* env variables first.') }}
        </p>
    @endif

    <input id="video_file" type="file" accept="video/*" class="input" @change="upload($event.target.files[0])">

    {{-- Progress/status --}}
    <div class="mt-3" x-show="uploading" x-cloak>
        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>{{ __('Uploading…') }} <span x-text="filename"></span></span>
            <span class="font-mono" x-text="(percent|0) + '%'"></span>
        </div>
        <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
            <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-fuchsia-500 transition-all duration-150" :style="'width:' + percent + '%'"></div>
        </div>
    </div>

    <div class="mt-3 flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400" x-show="uploaded" x-cloak>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span>{{ __('Upload complete — the file is now in Cloudflare. Save the lesson to confirm.') }}</span>
    </div>

    <div class="mt-2 text-sm text-rose-500" x-show="error" x-text="error" x-cloak></div>

    {{-- The R2 object key is saved into video_path so the normal lesson store/update persists it. --}}
    <input type="hidden" name="video_path" id="video_path" value="{{ old('video_path', $lesson->video_path) }}">
    @if($lesson->video_path)
        <p class="mt-2 text-xs text-gray-400">{{ __('Current:') }} <span class="font-mono" dir="ltr">{{ $lesson->video_path }}</span></p>
    @endif
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('r2Upload', () => ({
            // Reactive state
            url: '',
            token: '',
            uploading: false,
            filename: '',
            percent: 0,
            uploaded: false,
            error: null,

            // Read config from data-* attributes (no HTML-attribute quote collisions).
            init() {
                this.url = this.$el.dataset.url;
                this.token = this.$el.dataset.token;
            },

            reset() {
                this.percent = 0;
                this.uploading = false;
                this.uploaded = false;
                this.error = null;
                this.filename = '';
            },

            async upload(file) {
                if (! file) return;
                this.reset();
                this.filename = file.name;
                this.uploading = true;

                try {
                    // 1) Ask Laravel for a presigned PUT URL + the final R2 key.
                    const res = await fetch(this.url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            filename: file.name,
                            content_type: file.type || 'video/mp4',
                        }),
                    });

                    if (! res.ok) throw new Error(await res.text());
                    const { url, key } = await res.json();

                    // 2) PUT the file straight to Cloudflare R2 — never touches our server.
                    await this.put(url, file);
                    this.percent = 100;

                    // 3) Save the R2 key into the form's video_path field.
                    document.getElementById('video_path').value = key;
                    this.uploaded = true;
                } catch (e) {
                    this.error = (e && e.message) || '{{ __('Upload failed. Please try again.') }}';
                } finally {
                    this.uploading = false;
                }
            },

            // Upload with progress via XMLHttpRequest (fetch has no upload progress).
            put(url, file) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('PUT', url, true);
                    xhr.setRequestHeader('Content-Type', file.type || 'video/mp4');
                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) this.percent = (e.loaded / e.total) * 100;
                    };
                    xhr.onload = () => (xhr.status >= 200 && xhr.status < 300) ? resolve() : reject(new Error('{{ __('Upload failed') }} (' + xhr.status + ')'));
                    xhr.onerror = () => reject(new Error('{{ __('Network error') }}'));
                    xhr.send(file);
                });
            },
        }));
    });
</script>