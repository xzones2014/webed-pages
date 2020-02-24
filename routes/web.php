<?php
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

$adminRoute = config('webed.admin_route');

$namespace = 'WebEd\Base\Pages\Http\Controllers';

/*
 * Admin route
 * */
Route::group(['prefix' => $adminRoute . '/pages', 'namespace' => $namespace], function (Router $router) {
    $router->get('/', 'PageController@getIndex')
        ->name('admin::pages.index.get')
        ->middleware('has-permission:view-pages');

    $router->post('/', 'PageController@postListing')
        ->name('admin::pages.index.post')
        ->middleware('has-permission:view-pages');

    $router->post('update-status/{id}/{status}', 'PageController@postUpdateStatus')
        ->name('admin::pages.update-status.post')
        ->middleware('has-permission:edit-pages');

    $router->get('create', 'PageController@getCreate')
        ->name('admin::pages.create.get')
        ->middleware('has-permission:create-pages');

    $router->post('create', 'PageController@postCreate')
        ->name('admin::pages.create.post')
        ->middleware('has-permission:create-pages');

    $router->get('edit/{id}', 'PageController@getEdit')
        ->name('admin::pages.edit.get')
        ->middleware('has-permission:view-pages');

    $router->post('edit/{id}', 'PageController@postEdit')
        ->name('admin::pages.edit.post')
        ->middleware('has-permission:edit-pages');

    $router->delete('/{id}', 'PageController@deleteDelete')
        ->name('admin::pages.delete.delete')
        ->middleware('has-permission:delete-pages');
});

/**
 * Front site
 */
foreach (config('webed-pages.custom_route_locations.web', []) as $file) {
    require $file;
}
foreach (config('webed-pages.public_routes.web', []) as $method => $routeInfo) {
    foreach ($routeInfo as $item) {
        Route::$method($item[0], $item[1]);
    }
}
