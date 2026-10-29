@props(['item'])

@php
    $dialogId = 'delete-item-dialog-'.$item->getKey();
    $titleId = $dialogId.'-title';
    $descriptionId = $dialogId.'-description';
@endphp

<div
    x-data="{
        open: false,
        showDialog() {
            this.open = true;
            this.$nextTick(() => this.$refs.cancel.focus());
        },
        closeDialog() {
            this.open = false;
            this.$nextTick(() => this.$refs.trigger.focus());
        },
        trapFocus(event) {
            if (event.shiftKey && document.activeElement === this.$refs.cancel) {
                event.preventDefault();
                this.$refs.confirm.focus();
            } else if (! event.shiftKey && document.activeElement === this.$refs.confirm) {
                event.preventDefault();
                this.$refs.cancel.focus();
            }
        }
    }"
    x-on:keydown.escape.window="if (open) closeDialog()"
>
    <button
        x-ref="trigger"
        type="button"
        x-on:click="showDialog()"
        aria-haspopup="dialog"
        aria-controls="{{ $dialogId }}"
        :aria-expanded="open.toString()"
        {{ $attributes }}
    >{{ $slot }}</button>

    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.200ms
        x-on:click.self="closeDialog()"
        x-on:keydown.tab="trapFocus($event)"
        class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-900/40 p-4"
    >
        <section
            id="{{ $dialogId }}"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="{{ $titleId }}"
            aria-describedby="{{ $descriptionId }}"
            tabindex="-1"
            class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-xl sm:p-7"
        >
            <div class="flex gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-700" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3l-7.5-13.1a2 2 0 0 0-3.4 0Z" />
                    </svg>
                </div>
                <div>
                    <h2 id="{{ $titleId }}" class="text-lg font-semibold text-slate-900">Delete this item?</h2>
                    <p id="{{ $descriptionId }}" class="mt-1.5 text-sm leading-6 text-slate-600">This action cannot be undone. The item will only be deleted after you confirm.</p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    x-ref="cancel"
                    type="button"
                    x-on:click="closeDialog()"
                    class="inline-flex min-h-10 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2"
                >Cancel</button>

                <form method="POST" action="{{ route('items.destroy', $item) }}">
                    @csrf
                    @method('DELETE')
                    <button
                        x-ref="confirm"
                        type="submit"
                        class="inline-flex min-h-10 w-full items-center justify-center rounded-md bg-red-700 px-4 text-sm font-semibold text-white hover:bg-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-700 focus-visible:ring-offset-2 sm:w-auto"
                    >Delete Item</button>
                </form>
            </div>
        </section>
    </div>
</div>
