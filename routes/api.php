<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Public API Routes (No Authentication Required)
Route::group(['namespace' => 'Api'], function () {
    Route::get('/public-test', function() {
        return response()->json([
            'status' => true,
            'message' => 'This is a public API endpoint without authentication.'
        ]);
    });
    
    // You can add your controllers here, e.g.:
    // Route::get('/products', 'ProductController@index');
});
