<?php

namespace App\Repositories\Contracts;

use App\Models\Item;
use Illuminate\Pagination\LengthAwarePaginator;

interface ItemRepositoryInterface
{
    public function paginate(?string $search = null): LengthAwarePaginator;

    public function find(int $id): Item;

    /** @param array{name: string, description?: string|null, price: string|int|float} $data */
    public function create(array $data): Item;

    /** @param array{name: string, description?: string|null, price: string|int|float} $data */
    public function update(Item $item, array $data): Item;

    public function delete(Item $item): void;

    public function isUsedInOrder(int $id): bool;
}
