@if (session('success') || session('error'))
    @php($isError = session()->has('error'))

    <div class="mx-auto max-w-6xl px-5 pt-5 sm:px-8" x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 4000)">
        <div
            x-cloak
            x-show="visible"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            role="{{ $isError ? 'alert' : 'status' }}"
            aria-live="{{ $isError ? 'assertive' : 'polite' }}"
            aria-atomic="true"
            class="flex items-start gap-3 rounded-lg border px-4 py-3 text-sm shadow-sm {{ $isError ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}"
        >
            @if ($isError)
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" d="M12 8v5m0 3h.01" />
                </svg>
                <p class="min-w-0 flex-1">{{ session('error') }}</p>
            @else
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16 9" />
                </svg>
                <p class="min-w-0 flex-1">{{ session('success') }}</p>
            @endif

            <button
                type="button"
                x-on:click="visible = false"
                aria-label="Dismiss notification"
                class="-mr-1 -mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-current/70 hover:bg-white/70 hover:text-current focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700"
            >
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>
    </div>
@endif
