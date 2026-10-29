<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate text-lg font-semibold tracking-tight text-slate-900">Item Details</h1>
    </x-slot>

    <div class="mx-auto max-w-4xl px-5 py-8 sm:px-8 sm:py-10">
        <a href="{{ route('items.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">← Back to Items</a>

        <section class="mt-5 rounded-md border border-slate-200 bg-white p-6 sm:p-8" aria-labelledby="item-name">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-700">Item details</p>
                    <h2 id="item-name" class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $item->name }}</h2>
                    <p class="mt-3 text-2xl font-semibold text-slate-900">{{ number_format((float) $item->price, 2) }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('items.edit', $item) }}" class="inline-flex min-h-10 items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Edit Item</a>
                    <x-item-delete-confirmation :item="$item" class="inline-flex min-h-10 items-center justify-center rounded-md border border-red-200 bg-white px-4 text-sm font-semibold text-red-700 hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-700 focus-visible:ring-offset-2">Delete Item</x-item-delete-confirmation>
                </div>
            </div>

            <dl class="mt-8 grid gap-6 border-t border-slate-200 pt-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-slate-500">Description</dt>
                    <dd class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-800">{{ $item->description ?: 'No description provided.' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Created</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $item->created_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Last updated</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $item->updated_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                </div>
            </dl>
        </section>
    </div>
</x-app-layout>
