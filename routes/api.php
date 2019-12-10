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

$api = app('Dingo\Api\Routing\Router');
$api->group(['version' => 'v1','namespace' => 'App\\V1\\Api\\Controllers','prefix' => 'api/app'], function ($api) {
    $api->get('test', 'BaseController@test');
});
$api->group(['version' => 'v1','namespace' => 'App\\V1\\Admin\\Controllers','prefix' => 'api/admin'], function ($api) {
    $api->get('test', 'BaseController@test');
});
