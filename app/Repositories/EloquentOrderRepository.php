<?php

namespace App\Repositories;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Override;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    private const PER_PAGE = 10;

    #[Override]
    public function paginate(int $userId, ?string $search, ?string $status): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $userId)
            ->with('user')
            ->when($search !== null && $search !== '', fn ($query) => $query->whereKey($search))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    #[Override]
    public function countForUser(int $userId): int
    {
        return Order::query()->where('user_id', $userId)->count();
    }

    #[Override]
    public function findWithItemsForUser(int $id, int $userId): Order
    {
        return Order::query()
            ->where('user_id', $userId)
            ->with(['user', 'orderItems.item'])
            ->findOrFail($id);
    }

    #[Override]
    public function findForUpdateForUser(int $id, int $userId): Order
    {
        return Order::query()
            ->where('user_id', $userId)
            ->with('orderItems')
            ->lockForUpdate()
            ->findOrFail($id);
    }

    #[Override]
    public function findItemsByIds(array $ids, bool $lockForUpdate = false): Collection
    {
        return Item::query()
            ->whereKey($ids)
            ->when($lockForUpdate, fn ($query) => $query->lockForUpdate())
            ->get()
            ->keyBy('id');
    }

    #[Override]
    public function allItems(): Collection
    {
        return Item::query()->orderBy('name')->get();
    }

    #[Override]
    public function create(array $data): Order
    {
        return Order::query()->create($data);
    }

    #[Override]
    public function createOrderItem(Order $order, array $data): OrderItem
    {
        return $order->orderItems()->create($data);
    }

    #[Override]
    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }

    #[Override]
    public function updateOrderItem(OrderItem $orderItem, array $data): void
    {
        $orderItem->update($data);
    }

    #[Override]
    public function deleteOrderItemsNotIn(Order $order, array $keptIds): void
    {
        $query = $order->orderItems();

        if ($keptIds !== []) {
            $query->whereNotIn('id', $keptIds);
        }

        $query->delete();
    }
}
