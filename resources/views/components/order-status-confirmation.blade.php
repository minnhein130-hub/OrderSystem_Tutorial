@props([
    'order',
    'action' => 'cancel',
    'title' => null,
    'description' => null,
    'confirmLabel' => null,
])

@php
    $isCancel = $action === 'cancel';
    $dialogId = $action.'-order-dialog-'.$order->getKey();
    $titleId = $dialogId.'-title';
    $descriptionId = $dialogId.'-description';
    $title ??= $isCancel ? 'Cancel order #'.$order->id.'?' : 'Complete order #'.$order->id.'?';
    $description ??= $isCancel
        ? 'The order will be marked cancelled and kept in your order history. This action cannot be undone.'
        : 'The order will be marked completed and can no longer be edited or cancelled.';
    $confirmLabel ??= $isCancel ? 'Cancel Order' : 'Complete Order';
    $buttonClass = $isCancel
        ? 'bg-red-700 hover:bg-red-800 focus-visible:ring-red-700'
        : 'bg-emerald-700 hover:bg-emerald-800 focus-visible:ring-emerald-700';
@endphp

<div
    x-data="{
        open: false,
        showDialog() { this.open = true; this.$nextTick(() => this.$refs.cancel.focus()); },
        closeDialog() { this.open = false; this.$nextTick(() => this.$refs.trigger.focus()); },
        trapFocus(event) {
            if (event.shiftKey && document.activeElement === this.$refs.cancel) {
                event.preventDefault(); this.$refs.confirm.focus();
            } else if (! event.shiftKey && document.activeElement === this.$refs.confirm) {
                event.preventDefault(); this.$refs.cancel.focus();
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
        class="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto bg-slate-900/40 p-4 sm:items-center"
    >
        <section
            id="{{ $dialogId }}"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="{{ $titleId }}"
            aria-describedby="{{ $descriptionId }}"
            tabindex="-1"
            class="my-auto max-h-[calc(100dvh-2rem)] w-full min-w-0 max-w-md overflow-y-auto break-words rounded-xl border border-slate-200 bg-white p-5 shadow-xl sm:p-7"
        >
            <h2 id="{{ $titleId }}" class="min-w-0 whitespace-normal break-words text-lg font-semibold text-slate-900">{{ $title }}</h2>
            <p id="{{ $descriptionId }}" class="mt-2 min-w-0 whitespace-normal break-words text-sm leading-6 text-slate-600">{{ $description }}</p>

            <div class="mt-6 flex w-full min-w-0 flex-col-reverse gap-3 sm:flex-row sm:flex-wrap sm:justify-end">
                <button
                    x-ref="cancel"
                    type="button"
                    x-on:click="closeDialog()"
                    class="inline-flex min-h-10 w-full items-center justify-center whitespace-normal break-words rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2 sm:w-auto"
                >Go Back</button>

                <form method="POST" action="{{ route('orders.'.$action, $order) }}" class="w-full min-w-0 sm:w-auto">
                    @csrf
                    @method('PATCH')
                    <button
                        x-ref="confirm"
                        type="submit"
                        class="inline-flex min-h-10 w-full max-w-full items-center justify-center whitespace-normal break-words rounded-md px-4 text-sm font-semibold text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 sm:w-auto {{ $buttonClass }}"
                    >{{ $confirmLabel }}</button>
                </form>
            </div>
        </section>
    </div>
</div>
