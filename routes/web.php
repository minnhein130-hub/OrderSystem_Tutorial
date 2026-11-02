<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (Request $request, OrderService $orderService) {
    return view('dashboard', [
        'ordersCount' => $orderService->countForUser((int) $request->user()->getAuthIdentifier()),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('items', ItemController::class)
    ->middleware('auth')
    ->whereNumber('item');

Route::middleware('auth')->group(function () {
    Route::resource('orders', OrderController::class)->except('destroy')->whereNumber('order');
    Route::patch('orders/{order}/complete', [OrderController::class, 'complete'])
        ->whereNumber('order')
        ->name('orders.complete');
    Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])
        ->whereNumber('order')
        ->name('orders.cancel');
});

require __DIR__.'/auth.php';
