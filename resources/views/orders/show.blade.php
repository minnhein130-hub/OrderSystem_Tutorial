<x-app-layout>
    <x-slot name="header">
        <h1 class="truncate text-lg font-semibold tracking-tight text-slate-900">Order #{{ $order->id }}</h1>
    </x-slot>

    <div class="mx-auto max-w-5xl px-5 py-8 sm:px-8 sm:py-10">
        <a href="{{ route('orders.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">← Back to Orders</a>

        <section class="mt-5 rounded-md border border-slate-200 bg-white p-6 sm:p-8" aria-labelledby="order-title">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-700">Order details</p>
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <h2 id="order-title" class="text-3xl font-semibold tracking-tight text-slate-900">Order #{{ $order->id }}</h2>
                        <x-orders.status-badge :status="$order->status" />
                    </div>
                    <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $order->total_amount }}</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    @if ($order->isPending())
                        @can('update', $order)
                            <a href="{{ route('orders.edit', $order) }}" class="inline-flex min-h-10 items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Edit Order</a>
                        @endcan
                        @can('complete', $order)
                            <x-order-status-confirmation :order="$order" action="complete" class="inline-flex min-h-10 items-center justify-center rounded-md border border-emerald-200 bg-white px-4 text-sm font-semibold text-emerald-800 hover:bg-emerald-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">Complete</x-order-status-confirmation>
                        @endcan
                        @can('cancel', $order)
                            <x-order-status-confirmation :order="$order" action="cancel" class="inline-flex min-h-10 items-center justify-center rounded-md border border-red-200 bg-white px-4 text-sm font-semibold text-red-700 hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-700 focus-visible:ring-offset-2">Cancel Order</x-order-status-confirmation>
                        @endcan
                    @endif
                </div>
            </div>

            <dl class="mt-8 grid gap-6 border-t border-slate-200 pt-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-slate-500">Created by</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $order->user->name }} <span class="text-slate-500">({{ $order->user->email }})</span></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Created date</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $order->created_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Last updated</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $order->updated_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                </div>
            </dl>

            <div class="mt-8 border-t border-slate-200 pt-6">
                <h3 class="text-base font-semibold text-slate-900">Ordered items</h3>
                <div class="mt-4 overflow-x-auto rounded-md border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                            <tr>
                                <th scope="col" class="px-5 py-3.5">Item</th>
                                <th scope="col" class="px-5 py-3.5 text-right">Quantity</th>
                                <th scope="col" class="px-5 py-3.5 text-right">Historical Unit Price</th>
                                <th scope="col" class="px-5 py-3.5 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($order->orderItems as $line)
                                <tr>
                                    <th scope="row" class="px-5 py-4 font-medium text-slate-900">{{ $line->item->name }}</th>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-slate-700">{{ $line->quantity }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-slate-700">{{ $line->unit_price }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right font-medium text-slate-900">{{ $line->subtotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <th scope="row" colspan="3" class="px-5 py-4 text-right font-semibold text-slate-900">Grand Total</th>
                                <td class="whitespace-nowrap px-5 py-4 text-right text-base font-semibold text-slate-900">{{ $order->total_amount }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
