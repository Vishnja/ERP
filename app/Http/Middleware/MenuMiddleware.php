<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Gate;
use Log;
use Menu;
use View;

class MenuMiddleware
{
    public $menu;

    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
    }

    public function handle($request, Closure $next)
    {
        /* Custom menu class */
        $menu = $this->menu;
        $menu->init( 'sidebar-menu', [
            'Главная'       => [ 'dashboard', 'fa-dashboard' ],
            'Заказы'        => [ 'orders.index', 'fa-shopping-cart' ],
            'Закупки'       => [ 'purchases.index', 'fa-shopping-cart' ],
            'Деньги'        => [ 'money.index', 'fa-shopping-cart' ],
            'Пользователи / Роли'  => [ null, 'fa-folder', [
                'Пользователи'              => [ 'users.index', 'fa-shopping-cart' ],
                'Роли'                      => [ 'roles.index', 'fa-shopping-cart' ]
            ]],
            'Справочники'   => [ null, 'fa-folder', [
                'Поставщики'                => 'suppliers.index',
                'Покупатели'                => 'buyers.index',
                'Товары'                    => 'products.index',
                'Товары Поставщ.'           => 'productSupplierPrice.index',
                'Статьи Прих. / Расх.'      => 'incomeExpenseItems.index',
            ]],
        ]);

        /* ACL */
        // Allow requests not listed in ACL,
        // because ajax requests generate routes like users.show which are not in ACL list.
        // Only protection now is not allowing to load index pages through normal requests
        if ($menu->routeInActualMenuItems() && Gate::denies('access', $menu->routeNameWithDashes))
            abort(403, 'Access denied.');

        return $next($request);
    }
}
