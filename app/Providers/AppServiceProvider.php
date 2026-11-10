<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Mechanic;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Vehicle;
use App\Observers\ActivityLogObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        foreach ([
            User::class,
            Category::class,
            Customer::class,
            Mechanic::class,
            Product::class,
            Setting::class,
            Transaction::class,
            TransactionItem::class,
            Vehicle::class,
        ] as $model) {
            $model::observe(ActivityLogObserver::class);
        }
    }
}
