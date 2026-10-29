<?php

namespace App\Repositories;

use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Override;

class EloquentItemRepository implements ItemRepositoryInterface
{
    private const PER_PAGE = 10;

    #[Override]
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return Item::query()
            ->when($search !== null && $search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    #[Override]
    public function find(int $id): Item
    {
        return Item::query()->findOrFail($id);
    }

    #[Override]
    public function create(array $data): Item
    {
        return Item::query()->create($data);
    }

    #[Override]
    public function update(int $id, array $data): Item
    {
        $item = $this->find($id);
        $item->update($data);

        return $item->refresh();
    }

    #[Override]
    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    #[Override]
    public function isUsedInOrder(int $id): bool
    {
        if (! Schema::hasTable('order_items')) {
            return false;
        }

        if (! Schema::hasColumn('order_items', 'item_id')) {
            throw new LogicException('The order_items table must have an item_id column before item deletion can be checked.');
        }

        return DB::table('order_items')->where('item_id', $id)->exists();
    }
}
