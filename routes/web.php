<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Services\ItemService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::bind('item', fn (string $value) => app(ItemService::class)->find((int) $value));
Route::resource('items', ItemController::class)->middleware('auth');
require __DIR__.'/auth.php';
