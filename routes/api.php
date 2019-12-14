<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$api = app('Dingo\Api\Routing\Router');
$api->group(['version' => 'v1', 'namespace' => 'App\\V1\\Admin\\Controllers', 'prefix' => 'api/admin'], function ($api) {
    //admin接口文档
    $api->get('swagger', 'BaseController@index');

    $api->get('captcha', 'BaseController@getCaptcha');
    $api->post('captcha', 'BaseController@validateCaptcha');

    $api->post('token', 'UserController@login');

    $api->group(['middleware' => 'auth:api'], function ($api) {
        $api->post('files', 'BaseController@uploadFile');

        $api->delete('token', 'UserController@loginOut');
        $api->put('token', 'UserController@loginOut');

        $api->get('category','ArticlesController@getCategoryList');
        $api->post('category','ArticlesController@postCategory');
        $api->delete('category/{id}','ArticlesController@deleteCategory');
        $api->get('category/{id}','ArticlesController@getCategoryDetail');
        $api->put('category/{id}','ArticlesController@putCategory');

        $api->get('articles/{page}/{limit}','ArticlesController@getArticlesList');
        $api->post('articles','ArticlesController@postArticles');
        $api->delete('articles/{id}','ArticlesController@deleteArticles');
        $api->get('articles/{id}','ArticlesController@getArticles');
        $api->put('articles/{id}','ArticlesController@putArticles');
    });

    app('Dingo\Api\Auth\Auth')->extend('basic', function ($app) {
        return new Dingo\Api\Auth\Provider\Basic($app['auth'], 'email');
    });
});

$api->group(['version' => 'v1', 'namespace' => 'App\\V1\\App\\Controllers', 'prefix' => 'api/app'], function ($api) {
    $api->get('test', 'BaseController@test');
});
