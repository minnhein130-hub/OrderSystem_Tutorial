<?php

namespace App\Repositories\Contracts;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function paginate(int $userId, ?string $search, ?string $status): LengthAwarePaginator;

    public function countForUser(int $userId): int;

    public function findWithItemsForUser(int $id, int $userId): Order;

    public function findForUpdateForUser(int $id, int $userId): Order;

    /** @param array<int, int> $ids @return Collection<int, Item> */
    public function findItemsByIds(array $ids, bool $lockForUpdate = false): Collection;

    /** @return Collection<int, Item> */
    public function allItems(): Collection;

    /** @param array{user_id: int, status: mixed, total_amount: string} $data */
    public function create(array $data): Order;

    /** @param array{item_id: int, quantity: int, unit_price: string, subtotal: string} $data */
    public function createOrderItem(Order $order, array $data): OrderItem;

    /** @param array{status?: mixed, total_amount?: string} $data */
    public function update(Order $order, array $data): Order;

    /** @param array{item_id: int, quantity: int, unit_price: string, subtotal: string} $data */
    public function updateOrderItem(OrderItem $orderItem, array $data): void;

    /** @param array<int, int> $keptIds */
    public function deleteOrderItemsNotIn(Order $order, array $keptIds): void;
}
