<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate text-lg font-semibold tracking-tight text-slate-900">Dashboard</h1>
    </x-slot>

    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8 sm:py-10">
        <section aria-labelledby="welcome-heading">
            <p class="text-sm font-semibold text-blue-700">Your workspace</p>
            <h2 id="welcome-heading" class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                Welcome back, {{ Auth::user()->name }}
            </h2>
            <p class="mt-3 text-base leading-7 text-slate-600">Manage your items and orders from one place.</p>
        </section>

        <section aria-label="Overview" class="mt-8 grid gap-5 md:grid-cols-2">
            <article class="rounded-md border border-slate-200 bg-white p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Items Overview</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Browse and manage the items in your catalog.</p>
                    </div>
                    <a href="{{ route('items.index') }}" class="shrink-0 rounded-md px-2.5 py-1 text-sm font-semibold text-blue-700 hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">View Items</a>
                </div>
            </article>

            <article class="rounded-md border border-slate-200 bg-white p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Orders Overview</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Your order summary will appear here when order management is available.</p>
                    </div>
                    <span class="shrink-0 rounded-md bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-500">Coming soon</span>
                </div>
            </article>
        </section>

        <section aria-labelledby="quick-actions-heading" class="mt-8 rounded-md border border-slate-200 bg-white p-6 sm:p-7">
            <div>
                <h3 id="quick-actions-heading" class="text-lg font-semibold tracking-tight text-slate-900">Quick Actions</h3>
                <p class="mt-1 text-sm text-slate-600">Open the Items section or add a new item to your catalog.</p>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('items.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">View Items</a>
                <a href="{{ route('items.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Add New Item</a>
                <span aria-disabled="true" class="inline-flex min-h-11 cursor-not-allowed items-center justify-center gap-3 rounded-md border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-500">
                    View Orders <span class="text-xs font-medium text-slate-400">Coming soon</span>
                </span>
                <span aria-disabled="true" class="inline-flex min-h-11 cursor-not-allowed items-center justify-center gap-3 rounded-md border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-500">
                    Create Order <span class="text-xs font-medium text-slate-400">Coming soon</span>
                </span>
            </div>
        </section>
    </div>
</x-app-layout>
