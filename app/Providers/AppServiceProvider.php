<?php

namespace App\Providers;

use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Repositories\EloquentItemRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ItemRepositoryInterface::class,
            EloquentItemRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
