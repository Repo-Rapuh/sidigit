<div x-data="{
    toasts: [],
    addToast(detail) {
        const toast = {
            id: Date.now() + Math.random(),
            message: detail?.message ?? '',
            type: detail?.type ?? 'success',
            duration: detail?.duration ?? 3000,
        };

        this.toasts.push(toast);

        setTimeout(() => {
            this.toasts = this.toasts.filter(t => t.id !== toast.id);
        }, toast.duration);
    },
    typeClass(type) {
        if (type === 'danger' || type === 'error') return 'bg-red-600 text-white';
        if (type === 'warning') return 'bg-yellow-500 text-gray-900';
        if (type === 'info') return 'bg-gray-800 text-white';
        return 'bg-green-600 text-white';
    },
}" x-on:toast.window="addToast($event.detail)" class="fixed top-4 right-4 z-[100000] space-y-2">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
            :class="typeClass(toast.type)"
            class="relative overflow-hidden rounded-lg px-4 py-4 shadow-lg text-sm w-[320px]">
            <div class="flex items-start gap-3">
                <span class="mt-0.5">
                    <x-lucide-check x-show="toast.type === 'success'" class="w-5 h-5" />
                    <x-lucide-triangle-alert x-show="toast.type === 'warning'" class="w-5 h-5" />
                    <x-lucide-info x-show="toast.type === 'info'" class="w-5 h-5" />
                    <x-lucide-alert-circle x-show="toast.type === 'danger' || toast.type === 'error'" class="w-5 h-5" />
                </span>
                <span x-text="toast.message" class="leading-5"></span>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-black/10"></div>
            <div class="absolute bottom-0 left-0 h-1 bg-white/70"
                :style="`animation: toast-progress ${toast.duration}ms linear forwards;`"></div>
        </div>
    </template>
</div>
