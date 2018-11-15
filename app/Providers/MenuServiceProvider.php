<?php

namespace App\Providers;

use App\Classes\Menu;
use Illuminate\Support\ServiceProvider;
use Log;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Menu $menu)
    {
        // View composer
        \View::composer('templates.page', function($view) use($menu) {
            $view->with('menu', $menu->html);
            $view->with('route', $menu->routeNameWithDashes);

            // page title for non-menu pages,
            // otherwise Menu generates empty string
            // that replaces $pageTitle set in controller method
            // because view::composer fires after controller
            if ($menu->pageTitle) $view->with('pageTitle', $menu->pageTitle);
        });

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Menu::class);
    }
}
