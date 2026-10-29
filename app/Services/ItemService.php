<?php

namespace App\Services;

use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private ItemRepositoryInterface $itemRepository) {}

    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return $this->itemRepository->paginate($search);
    }

    public function find(int $id): Item
    {
        return $this->itemRepository->find($id);
    }

    /** @param array{name: string, description?: string|null, price: string|int|float} $data */
    public function create(array $data): Item
    {
        return $this->itemRepository->create($data);
    }

    /** @param array{name: string, description?: string|null, price: string|int|float} $data */
    public function update(int $id, array $data): Item
    {
        return $this->itemRepository->update($id, $data);
    }

    /**
     * Delete an item unless an order currently references it.
     *
     * @return bool True when the item was deleted, false when it is in use.
     */
    public function delete(int $id): bool
    {
        if ($this->itemRepository->isUsedInOrder($id)) {
            return false;
        }

        try {
            $this->itemRepository->delete($id);
        } catch (QueryException $exception) {
            // A restrictive order_items foreign key can catch a reference added
            // after the first check. Convert that case into the same safe result.
            if ($this->itemRepository->isUsedInOrder($id)) {
                return false;
            }

            throw $exception;
        }

        return true;
    }
}
