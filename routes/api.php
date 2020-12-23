<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;

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

Route::group([
    'namespace'     => '\App\Http\Controllers\Api',
    'middleware'    => []
], function (Router $router) {
    $router->get('order/{order}', 'OrderController@show');
    $router->get('order', 'OrderController@index');
    $router->post('order_pay/{order}', 'OrderController@pay');
    $router->post('order', 'OrderController@buy');
    $router->get('order/{order}/find', 'OrderController@find'); # 订单查询

    //微信授权
    $router->get('oauth_url', 'OauthController@get_oauth_redirect_url');
    $router->get('oauth', 'OauthController@oauth');
});
