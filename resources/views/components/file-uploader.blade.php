@props([
    'label',
    'property' => 'file',
    'storedFile' => null,
    'accept' => '',
    'maxSizeBytes' => null,
    'uploadAction',
    'removeAction' => null,
    'rounded' => false,
])

<div {{ $attributes->class('space-y-3') }}
    x-data="{
        file: null, preview: null, busy: false, progress: 0, dragDepth: 0, error: '', status: '',
        size(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1).replace(/\.0$/, '') + ' KB';
            return (bytes / 1048576).toFixed(1).replace(/\.0$/, '') + ' MB';
        },
        discard(input = this.$refs.input) {
            if (this.preview) URL.revokeObjectURL(this.preview);
            this.preview = null; this.file = null; input.value = '';
        },
        destroy() { if (this.preview) URL.revokeObjectURL(this.preview); },
        select(files) {
            if (this.busy) return;
            this.error = ''; this.status = '';
            if (files.length > 1) { this.error = @js(__('uploads.error.single')); return; }
            const file = files[0];
            if (!file) return;
            const accept = @js($accept);
            const allowed = !accept || accept.split(',').some(part => {
                part = part.trim().toLowerCase();
                if (part.startsWith('.')) return file.name.toLowerCase().endsWith(part);
                if (part.endsWith('/*')) return file.type.startsWith(part.slice(0, -1));
                return file.type === part;
            });
            if (!allowed) { this.error = @js(__('uploads.error.type')); return; }
            const max = @js($maxSizeBytes);
            if (max && file.size > max) { this.error = @js(__('uploads.error.size', ['size' => ':size'])).replace(':size', this.size(max)); return; }
            this.discard(); this.file = file;
            if (file.type.startsWith('image/') && file.type !== 'image/svg+xml') this.preview = URL.createObjectURL(file);
        },
        async upload() {
            if (!this.file || this.busy) return;
            const wire = this.$wire;
            const input = this.$refs.input;
            this.busy = true; this.error = ''; this.status = ''; this.progress = 0;
            try {
                await new Promise((resolve, reject) => wire.upload(@js($property), this.file, resolve, reject,
                    event => this.progress = event.detail.progress, reject));
                await wire[@js($uploadAction)]();
                if (wire.$errors.any()) return;
                this.discard(input); this.status = @js(__('uploads.status.uploaded'));
            } catch { this.error = @js(__('uploads.error.upload')); }
            finally { this.busy = false; }
        },
        async remove() {
            if (this.busy) return;
            const wire = this.$wire;
            const input = this.$refs.input;
            this.busy = true; this.error = ''; this.status = '';
            try {
                await wire[@js($removeAction)]();
                if (wire.$errors.any()) return;
                this.discard(input); this.status = @js(__('uploads.status.removed'));
            } catch { this.error = @js(__('uploads.error.remove')); }
            finally { this.busy = false; }
        }
    }"
    :aria-busy="busy"
>
    <flux:field>
        <flux:label for="{{ $property }}-upload">{{ $label }}</flux:label>
        <div data-slot="upload-dropzone" class="mt-2 flex min-h-48 flex-col items-center gap-4 rounded-lg border-2 border-dashed border-zinc-300 p-6 dark:border-zinc-700"
            :class="{ 'border-blue-500 bg-blue-500/5': dragDepth > 0, 'opacity-60': busy }"
            @dragenter.prevent="if (!busy && $event.dataTransfer.types.includes('Files')) dragDepth++"
            @dragover.prevent
            @dragleave.prevent="dragDepth = Math.max(0, dragDepth - 1)"
            @drop.prevent="dragDepth = 0; select($event.dataTransfer.files)"
        >
            <img x-cloak x-show="preview" :src="preview" alt="{{ $label }}" @class(['size-20 shrink-0 object-cover', 'rounded-full' => $rounded, 'rounded-md' => ! $rounded]) />
            <div x-show="!preview && !file">
                @if ($storedFile && $storedFile['thumbnailUrl'])
                    <img src="{{ $storedFile['thumbnailUrl'] }}" alt="{{ $storedFile['name'] }}" @class(['size-20 shrink-0 object-cover', 'rounded-full' => $rounded, 'rounded-md' => ! $rounded]) />
                @else
                    {{ $slot }}
                @endif
            </div>
            <flux:icon.document x-cloak x-show="file && !preview" class="size-12" />
            <div x-cloak x-show="file" class="max-w-full space-y-1 text-center text-sm">
                <p class="truncate font-medium" x-text="file?.name"></p>
                <p class="text-zinc-500" x-text="file ? size(file.size) : ''"></p>
                <p>{{ __('uploads.status.selected') }}</p>
            </div>
            <div x-show="!file" class="max-w-full space-y-1 text-center text-sm">
                @if ($storedFile)
                    <p class="truncate font-medium">{{ $storedFile['name'] }}</p>
                    <p class="text-zinc-500">{{ \Illuminate\Support\Number::fileSize($storedFile['sizeBytes'], precision: 1) }}</p>
                @else
                    <p class="text-zinc-500">{{ __('uploads.description.drop') }}</p>
                @endif
            </div>
            <flux:button type="button" @click="$refs.input.click()" x-bind:disabled="busy">{{ __('uploads.button.choose') }}</flux:button>
            <input x-ref="input" id="{{ $property }}-upload" type="file" accept="{{ $accept }}" class="sr-only" :disabled="busy" @change="select($event.target.files)" />
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
            <flux:button type="button" variant="primary" @click="upload()" x-bind:disabled="!file || busy">{{ __('uploads.button.upload') }}</flux:button>
            <flux:button type="button" x-cloak x-show="file" @click="discard()" x-bind:disabled="busy">{{ __('uploads.button.discard') }}</flux:button>
            @if ($storedFile && $removeAction)
                <flux:button type="button" @click="remove()" x-bind:disabled="busy">{{ __('uploads.button.remove') }}</flux:button>
            @endif
        </div>
        <div x-cloak x-show="busy" class="mt-3 space-y-2">
            <progress class="h-2 w-full" max="100" :value="progress"></progress>
            <p class="text-sm text-zinc-500" x-text="progress < 100 ? @js(__('uploads.status.uploading')) + ' ' + progress + '%' : @js(__('uploads.status.processing'))"></p>
        </div>
        <flux:error :name="$property" />
        <p x-cloak x-show="error" x-text="error" role="alert" class="mt-2 text-sm text-red-600 dark:text-red-400"></p>
        <p x-cloak x-show="status" x-text="status" role="status" aria-live="polite" class="mt-2 text-sm text-zinc-500"></p>
    </flux:field>
</div>
