<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');

    $router->resource('/articles/category', 'ArticlesCategoryController')->names('admin.articles.category');
    $router->resource('/articles/{article_id}/chapters', 'ArticlesChaptersController')->names('admin.articles.chapters');
    $router->resource('/articles', 'ArticlesController')->names('admin.articles');

    $router->resource('/webConfig', 'WebConfigController')->names('admin.webConfig');
    $router->resource('/adConfig', 'AdConfigController')->names('admin.adConfig');

});
