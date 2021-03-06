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
//$api->group(['middleware' => 'throttle:60,1', 'version' => 'v1', 'namespace' => 'App\\V1\\Admin\\Controllers', 'prefix' => 'api/admin'], function ($api) {
//    //admin接口文档
//    $api->get('swagger', 'IndexController@index');
//});

$api->group(['version' => 'v1', 'namespace' => 'App\\Api\\Controllers', 'prefix' => 'api'], function ($api) {
    //app接口文档
    $api->get('swagger', 'IndexController@index');
    $api->get('/test', 'IndexController@test');

    $api->group(['middleware' => 'throttle:2,1'], function ($api) {
        $api->post('/captcha/sms', 'IndexController@sendSms');
    });

    $api->get('/qr', 'IndexController@getQrCode');
    $api->group(['middleware' => 'apiCache'], function ($api) {
        $api->get('/ad', 'AdController@getAdList');
        $api->get('/config', 'IndexController@getConfig');

        $api->get('articles', 'ArticlesController@getList');
        $api->get('articles/category', 'ArticlesController@getCategory');
        $api->get('articles/category/{type}', 'ArticlesController@getPage');

        $api->get('articles/{article_id}/chapters', 'ArticlesController@getChapterList');
    });

    $api->put('/ad/{key}', 'AdController@clickAd');
    $api->get('articles/{article_id}', 'ArticlesController@getDetail');
    $api->get('articles/{article_id}/{id}', 'ArticlesController@getChapter');

    $api->get('captcha', 'IndexController@getCaptcha');
    $api->put('captcha', 'IndexController@validateCaptcha');
    $api->put('captcha/sms', 'IndexController@validateSms');

    $api->post('token', 'UserController@login');
    $api->post('user', 'UserController@register');
    $api->group(['middleware' => 'auth:app'], function ($api) {
        $api->post('files', 'IndexController@uploadFile');

        $api->delete('token', 'UserController@loginOut');
        $api->put('token', 'UserController@changePWD');
        $api->get('user', 'UserController@get');
        $api->put('user', 'UserController@put');

        $api->get('user/collect', 'UserController@getCollect');
        $api->post('user/collect', 'UserController@postCollect');
        $api->delete('user/collect/{id}', 'UserController@deleteCollect');
    });
});
