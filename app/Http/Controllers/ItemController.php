<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function __construct(private ItemService $itemService) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'trim','string', 'max:100'],
        ]);

        $items = $this->itemService->paginate($validated['search'] ?? null);

        return view('items.index', compact('items'));
    }

    public function create(): View
    {
        return view('items.create');
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        $this->itemService->create($request->validated());

        return redirect()
            ->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    public function show(Item $item): View
    {
        return view('items.show', compact('item'));
    }

    public function edit(Item $item): View
    {
        return view('items.edit', compact('item'));
    }

    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        $item = $this->itemService->update($item->id, $request->validated());

        return redirect()
            ->route('items.show', $item)
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        if (! $this->itemService->delete($item->id)) {
            return redirect()
                ->route('items.show', $item)
                ->with('error', 'This item cannot be deleted because it is used in an order.');
        }

        return redirect()
            ->route('items.index')
            ->with('success', 'Item deleted successfully.');
    }
}
