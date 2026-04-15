<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsOrderController;
use App\Http\Controllers\SmsPoolWebhookController;

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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::middleware('auth:api')->get('/user', 'UserController@AuthRouteAPI');

Route::post('/sms-orders', [SmsOrderController::class, 'store']);
Route::get('/sms-orders/{smsOrder}', [SmsOrderController::class, 'showOrder']);
Route::post('/webhooks/smspool', [SmsPoolWebhookController::class, 'handle']);
