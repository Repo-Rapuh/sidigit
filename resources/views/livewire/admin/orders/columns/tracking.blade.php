@php
    $trackingUrl = $row->tracking_url;
@endphp

<div x-data="{
    trackingUrl: @js($trackingUrl),
    copied: false,
    hideTimer: null,
    copyLink() {
        if (navigator.clipboard?.writeText && window.isSecureContext) {
            navigator.clipboard.writeText(this.trackingUrl)
                .then(() => this.showCopySuccess())
                .catch(() => this.fallbackCopy());
            return;
        }
        this.fallbackCopy();
    },
    fallbackCopy() {
        const area = document.createElement('textarea');
        area.value = this.trackingUrl;
        area.setAttribute('readonly', 'readonly');
        area.style.position = 'fixed';
        area.style.top = '-9999px';
        area.style.left = '-9999px';
        document.body.appendChild(area);
        area.focus();
        area.select();

        let copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (e) {
            copied = false;
        } finally {
            document.body.removeChild(area);
        }

        if (copied) {
            this.showCopySuccess();
            return;
        }

        window.prompt('Salin link tracking ini:', this.trackingUrl);
    },
    showCopySuccess() {
        this.copied = true;
        window.dispatchEvent(new CustomEvent('toast', {
            detail: {
                message: 'Link tracking berhasil disalin.',
                type: 'success',
                duration: 2200,
            }
        }));
        if (this.hideTimer) {
            clearTimeout(this.hideTimer);
        }
        this.hideTimer = setTimeout(() => {
            this.copied = false;
        }, 1200);
    }
}" class="flex items-center gap-2">
    <a href="{{ $trackingUrl }}" target="_blank" rel="noopener"
        class="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2 py-1 text-xs font-medium text-gray-700 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
        <x-lucide-external-link class="h-3.5 w-3.5" />
        <span>Lihat</span>
    </a>

    <button type="button" @click="copyLink()"
        class="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2 py-1 text-xs font-medium text-gray-700 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
        <x-lucide-copy class="h-3.5 w-3.5" />
        <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
    </button>
</div>
