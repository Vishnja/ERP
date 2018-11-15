<?php
/**
 *  Auth, Password
 */

// Authentication routes
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Password reset link request routes
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');

/**
 * ERP Pages
 */

// expanded / collapsed menu
view()->composer('templates.page', function ($view) {
    $class = config('sidebar_extended') == 'true' ? '' : 'sidebar-collapse';
    $view->with('sidebarExtendedClass', $class);
    $view->with('currentUser', auth()->user());
});

Route::group([ 'middleware' => ['auth', 'menu'] ], function(){

    // Dashboard
    Route::get('/', ['as' => 'dashboard', 'uses' => 'PageController@dashboard']);

    // Orders
    Route::get('orders/search', ['as' => 'orders.search', 'uses' => 'OrderController@search']);
    Route::get('orders/selectSearch',
               ['as' => 'orders.selectSearch', 'uses' => 'OrderController@selectSearch']);
    Route::get('orders/productsBalances',
               ['as' => 'order.productsBalances',
                'uses' => 'OrderController@productsBalances']);
    Route::get('orders/action', ['as' => 'orders.action', 'uses' => 'OrderController@action']);
    Route::get('orders/history/{id}', ['as' => 'orders.history', 'uses' => 'OrderController@history']);
    Route::resource('orders', 'OrderController');

    // Order Products
    Route::resource('orders.products', 'OrderProductController');

    // Products
    Route::get('products/search', ['as' => 'products.search', 'uses' => 'ProductController@search']);
    Route::get('products/selectSearch',
               ['as' => 'products.selectSearch', 'uses' => 'ProductController@selectSearch']);
    Route::get('products/action', ['as' => 'products.action', 'uses' => 'ProductController@action']);
    Route::get('products/history/{id}',
               ['as' => 'products.history', 'uses' => 'ProductController@history']);
    Route::resource('products', 'ProductController');

    // Buyers
    Route::get('buyers/search', ['as' => 'buyers.search', 'uses' => 'BuyerController@search']);
    Route::get('buyers/selectSearch',
               ['as' => 'buyers.selectSearch', 'uses' => 'BuyerController@selectSearch']);
    Route::get('buyers/action', ['as' => 'buyers.action', 'uses' => 'BuyerController@action']);
    Route::resource('buyers', 'BuyerController');

    // Suppliers
    Route::get('suppliers/search', ['as' => 'suppliers.search', 'uses' => 'SupplierController@search']);
    Route::get('suppliers/selectSearch',
               ['as' => 'suppliers.selectSearch', 'uses' => 'SupplierController@selectSearch']);
    Route::get('suppliers/action', ['as' => 'suppliers.action', 'uses' => 'SupplierController@action']);
    Route::resource('suppliers', 'SupplierController');

    // Product Supplier Price
    Route::get('productSupplierPrice/search',
               ['as' => 'productSupplierPrice.search', 'uses' => 'ProductSupplierPriceController@search']);
    Route::get('productSupplierPrice/selectSearch',
               ['as' => 'productSupplierPrice.selectSearch',
                'uses' => 'ProductSupplierPriceController@selectSearch']);
    Route::get('productSupplierPrice/action',
               ['as' => 'productSupplierPrice.action',
                'uses' => 'ProductSupplierPriceController@action']);
    Route::resource('productSupplierPrice', 'ProductSupplierPriceController');

    // IncomeExpenseItems
    Route::get('incomeExpenseItems/search',
               ['as' => 'incomeExpenseItems.search', 'uses' => 'IncomeExpenseItemController@search']);
    Route::get('incomeExpenseItem/selectSearch',
               ['as' => 'incomeExpenseItems.selectSearch',
                'uses' => 'IncomeExpenseItemController@selectSearch']);
    Route::get('incomeExpenseItem/action',
               ['as' => 'incomeExpenseItem.action',
                'uses' => 'IncomeExpenseItemController@action']);
    Route::resource('incomeExpenseItems', 'IncomeExpenseItemController');

    // Purchases
    Route::get('purchases/search', ['as' => 'purchases.search', 'uses' => 'PurchaseController@search']);
    Route::get('purchases/selectSearch',
               ['as' => 'purchases.selectSearch', 'uses' => 'PurchaseController@selectSearch']);
    Route::get('purchases/action', ['as' => 'purchases.action', 'uses' => 'PurchaseController@action']);
    Route::get('purchases/history/{id}', ['as' => 'purchases.history', 'uses' => 'PurchaseController@history']);
    Route::resource('purchases', 'PurchaseController');

    // Money
    Route::get('money/search', ['as' => 'money.search', 'uses' => 'MoneyController@search']);
    Route::get('money/action', ['as' => 'money.action', 'uses' => 'MoneyController@action']);
    Route::resource('money', 'MoneyController');

    // Users
    Route::get('users/search', ['as' => 'users.search', 'uses' => 'UserController@search']);
    Route::get('users/action', ['as' => 'users.action', 'uses' => 'UserController@action']);
    Route::resource('users', 'UserController');
    Route::get('users/profile/{id}', ['as' => 'showProfile', 'uses' => 'UserController@showProfile']);
    Route::post('users/profile/{id}', ['as' => 'updateProfile', 'uses' => 'UserController@updateProfile']);

    // User Image
    Route::resource('images', 'ImageController');

    // Roles
    Route::get('roles/search', ['as' => 'roles.search', 'uses' => 'RoleController@search']);
    Route::get('roles/action', ['as' => 'roles.action', 'uses' => 'RoleController@action']);
    Route::resource('roles', 'RoleController');

    // Validation
    Route::post('validation', ['as' => 'validation', 'uses' => 'ValidationController@validation']);

});

