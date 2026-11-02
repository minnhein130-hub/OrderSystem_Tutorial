<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderItemsException;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Exceptions\OrderNotEditableException;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Item;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Order::class);

        $validated = $request->validate([
            'search' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:pending,completed,cancelled'],
        ]);

        return view('orders.index', [
            'orders' => $this->orderService->paginate(
                (int) $request->user()->getAuthIdentifier(),
                isset($validated['search']) ? (string) $validated['search'] : null,
                $validated['status'] ?? null,
            ),
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Order::class);

        return view('orders.create', $this->orderFormData());
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        try {
            $order = $this->orderService->create((int) $request->user()->id, $request->validated());
        } catch (InvalidOrderItemsException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Order created successfully.');
    }

    public function show(Request $request, Order $order): View
    {
        Gate::authorize('view', $order);

        return view('orders.show', [
            'order' => $this->orderService->find($order->id, (int) $request->user()->getAuthIdentifier()),
        ]);
    }

    public function edit(Request $request, Order $order): View|RedirectResponse
    {
        Gate::authorize('update', $order);

        try {
            $order = $this->orderService->editableOrder($order->id, (int) $request->user()->getAuthIdentifier());
        } catch (OrderNotEditableException $exception) {
            return redirect()->route('orders.show', $order)->with('error', $exception->getMessage());
        }

        return view('orders.edit', $this->orderFormData($order));
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('update', $order);

        try {
            $order = $this->orderService->update(
                $order->id,
                (int) $request->user()->getAuthIdentifier(),
                $request->validated(),
            );
        } catch (OrderNotEditableException|InvalidOrderItemsException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('complete', $order);

        try {
            $this->orderService->complete($order->id, (int) $request->user()->getAuthIdentifier());
        } catch (InvalidOrderStatusTransitionException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Order completed successfully.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('cancel', $order);

        try {
            $this->orderService->cancel($order->id, (int) $request->user()->getAuthIdentifier());
        } catch (InvalidOrderStatusTransitionException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Order cancelled successfully.');
    }

    /** @return array{catalog: array<int, array{id: int, name: string, price: string}>, initialRows: array<int, array<string, int|string|null>>} */
    private function orderFormData(?Order $order = null): array
    {
        $oldRows = old('items');

        if (is_array($oldRows) && $oldRows !== []) {
            $initialRows = [];
            foreach (array_values($oldRows) as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $initialRows[] = [
                    'key' => 'order-row-'.$index,
                    'item_id' => is_scalar($row['item_id'] ?? null) ? (string) $row['item_id'] : '',
                    'quantity' => is_scalar($row['quantity'] ?? null) ? (string) $row['quantity'] : '1',
                    'order_item_id' => is_scalar($row['order_item_id'] ?? null) ? (int) $row['order_item_id'] : null,
                ];
            }
        } elseif ($order !== null) {
            $initialRows = $order->orderItems->values()->map(fn ($line, $index) => [
                'key' => 'order-row-'.$index,
                'item_id' => (string) $line->item_id,
                'quantity' => (string) $line->quantity,
                'order_item_id' => $line->id,
            ])->all();
        } else {
            $initialRows = [[
                'key' => 'order-row-0',
                'item_id' => '',
                'quantity' => '1',
                'order_item_id' => null,
            ]];
        }

        if ($initialRows === []) {
            $initialRows[] = [
                'key' => 'order-row-0',
                'item_id' => '',
                'quantity' => '1',
                'order_item_id' => null,
            ];
        }

        $catalog = $this->orderService->itemCatalog()
            ->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => (string) $item->price,
            ])
            ->values()
            ->all();

        return compact('catalog', 'initialRows') + ['order' => $order];
    }
}
