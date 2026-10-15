<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    $routes->resource('products', ['except' => 'new,edit']);

    // transaksi bersifat immutable: tidak ada update
    $routes->resource('transactions', ['except' => 'new,edit,update']);

    // sale_items read-only (dibuat lewat POST /api/transactions)
    $routes->get('sale-items', 'SaleItems::index');
    $routes->get('sale-items/(:num)', 'SaleItems::show/$1');
});
