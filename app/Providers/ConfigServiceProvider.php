<?php

namespace App\Providers;

use Cookie;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $items_per_page = isset($_COOKIE['items_per_page']) ? $_COOKIE['items_per_page'] : 25;

        $sidebar_extended = isset($_COOKIE['sidebar_extended']) ? $_COOKIE['sidebar_extended'] : 'true';

        config([
            'items_per_page' => $items_per_page,
            'sidebar_extended' => $sidebar_extended,
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
