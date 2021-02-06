<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;

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

//todo add log middleware
//todo Route::middleware('auth:api')

Route::middleware('throttle:200,1')
    ->middleware('log.route')
    ->middleware('token')
    ->group(function () {

        Route::get('items', [ItemController::class, 'list']);
        Route::get('items/{id}', [ItemController::class, 'get']);
        Route::post('items', [ItemController::class, 'create']);
        Route::patch('items/{id}', [ItemController::class, 'update']);
        Route::delete('items/{id}', [ItemController::class, 'delete']);

    });

