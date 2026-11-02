<div
    x-data="{
        catalog: @js($catalog),
        rows: @js($initialRows),
        errors: @js($errors->getMessages()),
        init() { this.rows = this.rows.map((row, index) => ({ ...row, key: row.key || `row-${index}` })); },
        addRow() { if (this.rows.length < 50) this.rows.push({ key: `row-${Date.now()}-${Math.random()}`, item_id: '', quantity: '1', order_item_id: null }); },
        removeRow(index) { if (this.rows.length > 1) this.rows.splice(index, 1); },
        selectedItem(row) { return this.catalog.find(item => String(item.id) === String(row.item_id)); },
        lineError(index, field) { return this.errors[`items.${index}.${field}`]?.[0] || ''; },
        cents(amount) {
            const parts = String(amount || '0').split('.');
            const fraction = (parts[1] || '').padEnd(2, '0').slice(0, 2);
            return (BigInt(parts[0] || '0') * 100n) + BigInt(fraction || '0');
        },
        formatCents(amount) {
            const value = BigInt(amount || 0);
            const whole = (value / 100n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            const fraction = (value % 100n).toString().padStart(2, '0');
            return `${whole}.${fraction}`;
        },
        unitPrice(row) { return this.selectedItem(row)?.price || '0.00'; },
        rowSubtotalCents(row) {
            const quantity = Number.parseInt(row.quantity, 10);
            return this.cents(this.unitPrice(row)) * BigInt(Number.isInteger(quantity) && quantity > 0 ? quantity : 0);
        },
        subtotal(row) { return this.formatCents(this.rowSubtotalCents(row)); },
        total() { return this.rows.reduce((sum, row) => sum + this.rowSubtotalCents(row), 0n); }
    }"
>
    <div class="overflow-x-auto rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                <tr>
                    <th scope="col" class="px-4 py-3">Item</th>
                    <th scope="col" class="px-4 py-3 text-right">Price</th>
                    <th scope="col" class="px-4 py-3">Quantity</th>
                    <th scope="col" class="px-4 py-3 text-right">Subtotal</th>
                    <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                <template x-for="(row, index) in rows" :key="row.key">
                    <tr class="align-top">
                        <td class="min-w-60 px-4 py-4">
                            @if ($order)
                                <input type="hidden" :name="`items[${index}][order_item_id]`" :value="row.order_item_id || ''">
                            @endif
                            <select
                                :name="`items[${index}][item_id]`"
                                x-model="row.item_id"
                                required
                                class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
                                :aria-label="`Item for order row ${index + 1}`"
                                :aria-invalid="lineError(index, 'item_id') ? 'true' : 'false'"
                            >
                                <option value="">Select an item</option>
                                <template x-for="item in catalog" :key="item.id">
                                    <option :value="String(item.id)" x-text="item.name"></option>
                                </template>
                            </select>
                            <p x-cloak x-show="lineError(index, 'item_id')" x-text="lineError(index, 'item_id')" class="mt-1.5 text-sm text-red-700"></p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-right font-medium text-slate-700" x-text="unitPrice(row)"></td>
                        <td class="w-36 px-4 py-4">
                            <input
                                :name="`items[${index}][quantity]`"
                                x-model="row.quantity"
                                type="number"
                                min="1"
                                max="10000"
                                step="1"
                                required
                                class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
                                :aria-label="`Quantity for order row ${index + 1}`"
                                :aria-invalid="lineError(index, 'quantity') ? 'true' : 'false'"
                            >
                            <p x-cloak x-show="lineError(index, 'quantity')" x-text="lineError(index, 'quantity')" class="mt-1.5 text-sm text-red-700"></p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-right font-semibold text-slate-900" x-text="subtotal(row)"></td>
                        <td class="whitespace-nowrap px-4 py-4 text-right">
                            <button
                                type="button"
                                x-on:click="removeRow(index)"
                                x-bind:disabled="rows.length === 1"
                                class="font-medium text-red-700 hover:text-red-800 disabled:cursor-not-allowed disabled:text-slate-300"
                                :aria-label="`Remove order row ${index + 1}`"
                            >Remove</button>
                        </td>
                    </tr>
                </template>
                <tr>
                    <td colspan="3" class="px-4 py-4 text-right text-sm font-semibold text-slate-700">Grand Total</td>
                    <td class="whitespace-nowrap px-4 py-4 text-right text-base font-semibold text-slate-900" x-text="formatCents(total())"></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($errors->has('items'))
        <p class="mt-2 text-sm text-red-700" role="alert">{{ $errors->first('items') }}</p>
    @endif
    @if ($catalog === [])
        <p class="mt-3 text-sm text-amber-800">Create an item in the catalog before creating an order.</p>
    @endif

    <button
        type="button"
        x-on:click="addRow()"
        x-bind:disabled="rows.length >= 50 || rows.length >= catalog.length"
        class="mt-4 inline-flex min-h-10 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 disabled:cursor-not-allowed disabled:text-slate-400"
    >Add Another Item</button>
</div>
