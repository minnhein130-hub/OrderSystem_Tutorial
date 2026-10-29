<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate text-lg font-semibold tracking-tight text-slate-900">New Item</h1>
    </x-slot>

    @php($item = null)

    <div class="mx-auto max-w-3xl px-5 py-8 sm:px-8 sm:py-10">
        <a href="{{ route('items.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">← Back to Items</a>
        <div class="mt-5 rounded-md border border-slate-200 bg-white p-6 sm:p-8">
            <p class="text-sm font-semibold text-blue-700">Inventory</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Create Item</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Add the name, description, and price for this item.</p>

            <form method="POST" action="{{ route('items.store') }}" class="mt-6">
                @csrf
                @include('items.partials.form')

                <div class="mt-7 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-md bg-blue-700 px-5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Create Item</button>
                    <a href="{{ route('items.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-md border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
