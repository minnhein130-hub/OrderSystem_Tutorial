<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderItemsException;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Exceptions\OrderNotEditableException;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private const MAX_LINES = 50;

    private const MAX_QUANTITY = 10000;

    public function __construct(private OrderRepositoryInterface $orderRepository) {}

    public function paginate(int $userId, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return $this->orderRepository->paginate($userId, $search, $status);
    }

    public function countForUser(int $userId): int
    {
        return $this->orderRepository->countForUser($userId);
    }

    public function find(int $id, int $userId): Order
    {
        return $this->orderRepository->findWithItemsForUser($id, $userId);
    }

    public function editableOrder(int $id, int $userId): Order
    {
        $order = $this->orderRepository->findWithItemsForUser($id, $userId);
        $this->ensurePending($order);

        return $order;
    }

    /** @return Collection<int, Item> */
    public function itemCatalog(): Collection
    {
        return $this->orderRepository->allItems();
    }

    /** @param array<int, array{item_id: int|string, quantity: int|string}> $data */
    public function create(int $userId, array $data): Order
    {
        $lines = $this->normalizeLines($data['items'] ?? [], allowExistingLine: false);

        return DB::transaction(function () use ($userId, $lines): Order {
            $items = $this->loadSelectedItems($lines, lockForUpdate: true);
            $preparedLines = [];
            $totalCents = 0;

            foreach ($lines as $line) {
                $item = $items->get($line['item_id']);
                $unitPriceCents = $this->moneyToCents((string) $item->price);
                $subtotalCents = $unitPriceCents * $line['quantity'];
                $totalCents += $subtotalCents;

                $preparedLines[] = [
                    'item_id' => $item->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $this->formatCents($unitPriceCents),
                    'subtotal' => $this->formatCents($subtotalCents),
                ];
            }

            $order = $this->orderRepository->create([
                'user_id' => $userId,
                'status' => OrderStatus::Pending,
                'total_amount' => $this->formatCents($totalCents),
            ]);

            foreach ($preparedLines as $line) {
                $this->orderRepository->createOrderItem($order, $line);
            }

            return $this->orderRepository->findWithItemsForUser($order->id, $userId);
        });
    }

    /** @param array<int, array{item_id: int|string, quantity: int|string, order_item_id?: int|string|null}> $data */
    public function update(int $id, int $userId, array $data): Order
    {
        $lines = $this->normalizeLines($data['items'] ?? [], allowExistingLine: true);

        return DB::transaction(function () use ($id, $userId, $lines): Order {
            $order = $this->orderRepository->findForUpdateForUser($id, $userId);
            $this->ensurePending($order);
            $existingLines = $order->orderItems->keyBy('id');

            foreach ($lines as $line) {
                if ($line['order_item_id'] !== null && ! $existingLines->has($line['order_item_id'])) {
                    throw new InvalidOrderItemsException('One of these order lines does not belong to this order.');
                }
            }

            $items = $this->loadSelectedItems($lines, lockForUpdate: true);
            $preparedLines = [];
            $keptLineIds = [];
            $totalCents = 0;

            foreach ($lines as $line) {
                /** @var Item $item */
                $item = $items->get($line['item_id']);
                /** @var OrderItem|null $existingLine */
                $existingLine = $line['order_item_id'] !== null
                    ? $existingLines->get($line['order_item_id'])
                    : null;

                $unitPriceCents = $existingLine !== null && (int) $existingLine->item_id === $item->id
                    ? $this->moneyToCents((string) $existingLine->unit_price)
                    : $this->moneyToCents((string) $item->price);
                $subtotalCents = $unitPriceCents * $line['quantity'];
                $totalCents += $subtotalCents;

                $preparedLines[] = [
                    'existing_line' => $existingLine,
                    'attributes' => [
                        'item_id' => $item->id,
                        'quantity' => $line['quantity'],
                        'unit_price' => $this->formatCents($unitPriceCents),
                        'subtotal' => $this->formatCents($subtotalCents),
                    ],
                ];

                if ($existingLine !== null) {
                    $keptLineIds[] = $existingLine->id;
                }
            }

            $this->orderRepository->update($order, [
                'total_amount' => $this->formatCents($totalCents),
            ]);

            foreach ($preparedLines as $line) {
                if ($line['existing_line'] instanceof OrderItem) {
                    $this->orderRepository->updateOrderItem($line['existing_line'], $line['attributes']);
                } else {
                    $this->orderRepository->createOrderItem($order, $line['attributes']);
                }
            }

            $this->orderRepository->deleteOrderItemsNotIn($order, $keptLineIds);

            return $this->orderRepository->findWithItemsForUser($id, $userId);
        });
    }

    public function complete(int $id, int $userId): Order
    {
        return $this->transition($id, $userId, OrderStatus::Completed);
    }

    public function cancel(int $id, int $userId): Order
    {
        return $this->transition($id, $userId, OrderStatus::Cancelled);
    }

    /**
     * @param array<int, array<string, mixed>> $submittedLines
     * @return array<int, array{item_id: int, quantity: int, order_item_id: int|null}>
     */
    private function normalizeLines(mixed $submittedLines, bool $allowExistingLine): array
    {
        if (! is_array($submittedLines) || $submittedLines === [] || count($submittedLines) > self::MAX_LINES) {
            throw new InvalidOrderItemsException('An order must contain between 1 and '.self::MAX_LINES.' items.');
        }

        $normalized = [];
        $seenItemIds = [];

        foreach ($submittedLines as $line) {
            if (! is_array($line)) {
                throw new InvalidOrderItemsException('Each order line must include an item and quantity.');
            }

            $itemId = filter_var($line['item_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($line['quantity'] ?? null, FILTER_VALIDATE_INT);

            if ($itemId === false || $itemId < 1 || $quantity === false || $quantity < 1 || $quantity > self::MAX_QUANTITY) {
                throw new InvalidOrderItemsException('Choose a valid item and a quantity between 1 and '.self::MAX_QUANTITY.'.');
            }

            if (isset($seenItemIds[$itemId])) {
                throw new InvalidOrderItemsException('Choose each item only once per order.');
            }
            $seenItemIds[$itemId] = true;

            $orderItemId = null;
            if ($allowExistingLine && isset($line['order_item_id']) && $line['order_item_id'] !== '') {
                $orderItemId = filter_var($line['order_item_id'], FILTER_VALIDATE_INT);
                if ($orderItemId === false || $orderItemId < 1) {
                    throw new InvalidOrderItemsException('An order line reference is invalid.');
                }
            }

            $normalized[] = [
                'item_id' => $itemId,
                'quantity' => $quantity,
                'order_item_id' => $orderItemId,
            ];
        }

        return $normalized;
    }

    /** @param array<int, array{item_id: int, quantity: int, order_item_id: int|null}> $lines */
    private function loadSelectedItems(array $lines, bool $lockForUpdate): Collection
    {
        $ids = array_values(array_unique(array_column($lines, 'item_id')));
        $items = $this->orderRepository->findItemsByIds($ids, $lockForUpdate);

        if ($items->count() !== count($ids)) {
            throw new InvalidOrderItemsException();
        }

        return $items;
    }

    private function transition(int $id, int $userId, OrderStatus $nextStatus): Order
    {
        return DB::transaction(function () use ($id, $userId, $nextStatus): Order {
            $order = $this->orderRepository->findForUpdateForUser($id, $userId);

            if ($order->status !== OrderStatus::Pending) {
                $message = match ([$order->status, $nextStatus]) {
                    [OrderStatus::Cancelled, OrderStatus::Completed] => 'Cancelled orders cannot be completed.',
                    [OrderStatus::Completed, OrderStatus::Cancelled] => 'Completed orders cannot be cancelled.',
                    default => 'Invalid order status transition.',
                };

                throw new InvalidOrderStatusTransitionException($message);
            }

            $this->orderRepository->update($order, ['status' => $nextStatus]);

            return $this->orderRepository->findWithItemsForUser($id, $userId);
        });
    }

    private function ensurePending(Order $order): void
    {
        if ($order->status !== OrderStatus::Pending) {
            throw new OrderNotEditableException($order->status);
        }
    }

    private function moneyToCents(string $amount): int
    {
        if (! preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $amount, $matches)) {
            throw new InvalidOrderItemsException('An item has an invalid catalog price.');
        }

        return ((int) $matches[1] * 100) + (int) str_pad($matches[2] ?? '', 2, '0');
    }

    private function formatCents(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
