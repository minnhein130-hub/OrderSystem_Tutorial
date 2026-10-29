<div>
    <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
    <input
        id="name"
        name="name"
        type="text"
        value="{{ old('name', $item?->name ?? '') }}"
        maxlength="255"
        required
        autofocus
        class="mt-1.5 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
    >
    @error('name')
        <p id="name-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>

<div class="mt-5">
    <label for="description" class="block text-sm font-medium text-slate-700">Description <span class="font-normal text-slate-500">(optional)</span></label>
    <textarea
        id="description"
        name="description"
        rows="4"
        class="mt-1.5 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
        @error('description') aria-invalid="true" aria-describedby="description-error" @enderror
    >{{ old('description', $item?->description ?? '') }}</textarea>
    @error('description')
        <p id="description-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>

<div class="mt-5">
    <label for="price" class="block text-sm font-medium text-slate-700">Price</label>
    <input
        id="price"
        name="price"
        type="number"
        value="{{ old('price', $item ? number_format((float) $item->price, 2, '.', '') : '') }}"
        min="0"
        max="9999999999.99"
        step="0.01"
        required
        class="mt-1.5 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700"
        @error('price') aria-invalid="true" aria-describedby="price-error" @enderror
    >
    <p class="mt-1.5 text-xs text-slate-500">Enter a non-negative amount with up to two decimal places.</p>
    @error('price')
        <p id="price-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
