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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::options('/{all}', function (Request $request) {
    $origin = $request->header('ORIGIN', '*');
    header("Access-Control-Allow-Origin: $origin");

    header('Content-Type: application/json; charset=utf-8');
    header("Access-Control-Allow-Credentials: true");
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, api_key, Authorization');
})->where(['all' => '([a-zA-Z0-9-]|/)+']);

$api = app('Dingo\Api\Routing\Router');
$api->group(['version' => 'v1', 'namespace' => 'App\\V1\\Admin\\Controllers', 'prefix' => 'api/admin'], function ($api) {
    $api->get('swagger', 'BaseController@index');

    $api->get('captcha', 'BaseController@getCaptcha');
    $api->post('captcha', 'BaseController@validateCaptcha');

    $api->post('token', 'UserController@login');
    $api->group(['middleware' => 'auth:api'], function($api){
        $api->delete('token', 'UserController@loginOut');
        $api->get('test', function (Request $request){
            return $request->bearerToken();
        });
    });

    app('Dingo\Api\Auth\Auth')->extend('basic', function ($app) {
        return new Dingo\Api\Auth\Provider\Basic($app['auth'], 'email');
    });
});

$api->group(['version' => 'v1', 'namespace' => 'App\\V1\\App\\Controllers', 'prefix' => 'api/app'], function ($api) {
    $api->get('test', 'BaseController@test');
});
