@php
    use Tiptap\Editor;

    $initialHtml = is_string($value ?? null) ? (string) $value : '';
    $decoded = json_decode($initialHtml, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        try {
            $initialHtml = (new Editor())->setContent($decoded)->getHTML();
        } catch (\Throwable $e) {
            // Fall back to raw content if rendering fails.
        }
    }
@endphp

<div
    x-data="{
        value: @entangle('value').live,
        initialHtml: @js($initialHtml),
        isEmpty: true,
        exec(cmd, arg = null) {
            document.execCommand(cmd, false, arg);
            this.updateValue();
        },
        setBlock(tag) {
            if (!tag) return;
            const block = tag === 'paragraph' ? 'p' : tag;
            document.execCommand('formatBlock', false, block);
            this.updateValue();
        },
        updateValue() {
            if (!this.$refs.editor) return;
            this.value = this.$refs.editor.innerHTML;
            this.isEmpty = this.$refs.editor.textContent.trim() === '';
        },
        syncFromValue() {
            if (!this.$refs.editor) return;
            const html = this.value || '';
            if (this.$refs.editor.innerHTML !== html) {
                this.$refs.editor.innerHTML = html;
            }
            this.isEmpty = this.$refs.editor.textContent.trim() === '';
        }
    }"
    x-init="
        $nextTick(() => {
            $refs.editor.innerHTML = initialHtml || '';
            updateValue();
        });
        $watch('value', () => {
            if (document.activeElement !== $refs.editor) {
                syncFromValue();
            }
        });
    "
    class="space-y-2"
    wire:ignore
>
    @if ($label)
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 p-2 dark:border-gray-800">
            <select @change="setBlock($event.target.value)"
                class="rounded-md border border-gray-200 bg-white px-2 py-1 text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                @foreach ($headings as $heading)
                    <option value="{{ $heading }}">
                        {{ $heading === 'paragraph' ? 'Paragraph' : strtoupper($heading) }}
                    </option>
                @endforeach
            </select>
            <button type="button" @click="exec('bold')" title="Bold"
                class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                B
            </button>
            <button type="button" @click="exec('italic')" title="Italic"
                class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                I
            </button>
            <button type="button" @click="exec('underline')" title="Underline"
                class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                U
            </button>
            <button type="button" @click="exec('insertUnorderedList')" title="Bullet list"
                class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                aria-label="Bullet list">
                <x-lucide-list class="h-4 w-4" />
            </button>
            <button type="button" @click="exec('insertOrderedList')" title="Numbered list"
                class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                aria-label="Numbered list">
                <x-lucide-list-ordered class="h-4 w-4" />
            </button>
            <button type="button" @click="exec('createLink', prompt('Masukkan URL:'))" title="Link"
                class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                aria-label="Link">
                <x-lucide-link class="h-4 w-4" />
            </button>
            <button type="button" @click="exec('removeFormat')" title="Clear formatting"
                class="rounded-md px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                aria-label="Clear formatting">
                <x-lucide-eraser class="h-4 w-4" />
            </button>
        </div>

        <div class="relative">
            <div x-show="isEmpty"
                class="pointer-events-none absolute left-3 top-3 text-sm text-gray-400 dark:text-gray-500"
                x-text="@js($placeholder)">
            </div>
            <div x-ref="editor" contenteditable
                class="min-h-[140px] px-3 py-3 text-sm text-gray-900 outline-none dark:text-gray-100"
                @input="updateValue" @blur="updateValue"></div>
        </div>
    </div>
</div>
