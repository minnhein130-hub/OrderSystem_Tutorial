<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate text-lg font-semibold tracking-tight text-slate-900">Items</h1>
    </x-slot>

    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8 sm:py-10">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-700">Inventory</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Items</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">View and manage the items available in your catalog.</p>
            </div>
            <a href="{{ route('items.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-md bg-blue-700 px-5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">
                New Item
            </a>
        </div>

        <section aria-label="Search items" class="mt-7 rounded-md border border-slate-200 bg-white p-4 sm:p-5">
            <form method="GET" action="{{ route('items.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <div class="flex-1">
                    <label for="search" class="sr-only">Search by item name or description</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ request('search') }}"
                        maxlength="100"
                        placeholder="Search by name or description"
                        class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
                        @error('search') aria-invalid="true" aria-describedby="search-error" @enderror
                    >
                    @error('search')
                        <p id="search-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">
                        Search
                    </button>
                    @if (request()->filled('search'))
                        <a href="{{ route('items.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section aria-label="Items list" class="mt-5 overflow-hidden rounded-md border border-slate-200 bg-white">
            @if ($items->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                            <tr>
                                <th scope="col" class="px-5 py-3.5">Item Name</th>
                                <th scope="col" class="px-5 py-3.5">Description</th>
                                <th scope="col" class="px-5 py-3.5">Price</th>
                                <th scope="col" class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($items as $item)
                                <tr class="align-top">
                                    <th scope="row" class="whitespace-nowrap px-5 py-4 font-semibold text-slate-900">
                                        <a href="{{ route('items.show', $item) }}" class="hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">{{ $item->name }}</a>
                                    </th>
                                    <td class="max-w-sm px-5 py-4 leading-6 text-slate-600">{{ $item->description ?: '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-800">{{ number_format((float) $item->price, 2) }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('items.show', $item) }}" class="font-medium text-blue-700 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">View</a>
                                            <a href="{{ route('items.edit', $item) }}" class="font-medium text-blue-700 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">Edit</a>
                                            <x-item-delete-confirmation :item="$item" class="font-medium text-red-700 hover:text-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-700">Delete</x-item-delete-confirmation>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($items->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">
                        {{ $items->links() }}
                    </div>
                @endif
            @else
                <div class="px-6 py-12 text-center">
                    <h3 class="text-base font-semibold text-slate-900">
                        {{ request()->filled('search') ? 'No matching items' : 'No items yet' }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ request()->filled('search') ? 'Try a different name or description, or clear your search.' : 'Create your first item to get started.' }}
                    </p>
                    @if (request()->filled('search'))
                        <a href="{{ route('items.index') }}" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Clear search</a>
                    @else
                        <a href="{{ route('items.create') }}" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">New Item</a>
                    @endif
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
