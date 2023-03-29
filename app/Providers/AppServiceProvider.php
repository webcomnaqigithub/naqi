<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Offer;
use App\Observers\OfferObserver;

use App\Models\Product;
use App\Observers\ProductObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Offer::observe(OfferObserver::class);
        Product::observe(ProductObserver::class);

    }
}
