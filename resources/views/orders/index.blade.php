<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate text-lg font-semibold tracking-tight text-slate-900">Orders</h1>
    </x-slot>

    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8 sm:py-10">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-700">Order management</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Orders</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Review orders, update pending orders, and track their status.</p>
            </div>
            <a href="{{ route('orders.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-md bg-blue-700 px-5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">
                New Order
            </a>
        </div>

        <section aria-label="Filter orders" class="mt-7 rounded-md border border-slate-200 bg-white p-4 sm:p-5">
            <form method="GET" action="{{ route('orders.index') }}" class="grid gap-3 sm:grid-cols-[1fr_14rem_auto]">
                <div>
                    <label for="search" class="sr-only">Search by order ID</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ request('search') }}"
                        inputmode="numeric"
                        placeholder="Search by order ID"
                        class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
                        @error('search') aria-invalid="true" aria-describedby="search-error" @enderror
                    >
                    @error('search')
                        <p id="search-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="status" class="sr-only">Filter by status</label>
                    <select id="status" name="status" class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" @error('status') aria-invalid="true" aria-describedby="status-error" @enderror>
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ ucfirst($status->value) }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p id="status-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Filter</button>
                    @if (request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('orders.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Clear</a>
                    @endif
                </div>
            </form>
        </section>

        <section aria-label="Orders list" class="mt-5 overflow-hidden rounded-md border border-slate-200 bg-white">
            @if ($orders->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                            <tr>
                                <th scope="col" class="px-5 py-3.5">Order ID</th>
                                <th scope="col" class="px-5 py-3.5">Created By</th>
                                <th scope="col" class="px-5 py-3.5">Total Amount</th>
                                <th scope="col" class="px-5 py-3.5">Status</th>
                                <th scope="col" class="px-5 py-3.5">Created</th>
                                <th scope="col" class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($orders as $order)
                                <tr class="align-middle">
                                    <th scope="row" class="whitespace-nowrap px-5 py-4 font-semibold text-slate-900">
                                        <a href="{{ route('orders.show', $order) }}" class="hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">#{{ $order->id }}</a>
                                    </th>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-700">{{ $order->user->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-800">{{ $order->total_amount }}</td>
                                    <td class="whitespace-nowrap px-5 py-4"><x-orders.status-badge :status="$order->status" /></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $order->created_at?->format('M j, Y g:i A') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('orders.show', $order) }}" class="font-medium text-blue-700 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">View</a>
                                            @if ($order->isPending())
                                                @can('update', $order)
                                                    <a href="{{ route('orders.edit', $order) }}" class="font-medium text-blue-700 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">Edit</a>
                                                @endcan
                                                @can('complete', $order)
                                                    <x-order-status-confirmation :order="$order" action="complete" class="font-medium text-emerald-700 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">Complete</x-order-status-confirmation>
                                                @endcan
                                                @can('cancel', $order)
                                                    <x-order-status-confirmation :order="$order" action="cancel" class="font-medium text-red-700 hover:text-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-700">Cancel</x-order-status-confirmation>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($orders->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $orders->links() }}</div>
                @endif
            @else
                <div class="px-6 py-12 text-center">
                    <h3 class="text-base font-semibold text-slate-900">{{ request()->filled('search') || request()->filled('status') ? 'No matching orders' : 'No orders yet' }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ request()->filled('search') || request()->filled('status') ? 'Try changing your filters.' : 'Create your first order to get started.' }}</p>
                    @if (! request()->filled('search') && ! request()->filled('status'))
                        <a href="{{ route('orders.create') }}" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">New Order</a>
                    @endif
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
