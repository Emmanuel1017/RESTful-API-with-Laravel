<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\RefreshController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\Api\ImageController;

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



Route::controller(RegisterController::class)
->middleware(['api', 'return-json']) // Use our JSON Middleware
->group(function () {
    Route::post('register', 'register');
});
Route::controller(LoginController::class)
->middleware(['api', 'return-json']) // Use our JSON Middleware
->group(function () {
    Route::post('login', 'login')->name('login');
});



Route::controller(ImageController::class)
->middleware(['api', 'return-json']) // Use our JSON Middleware
->group(function () {
    Route::post('profileimage', 'imageStore');
    Route::get('mydp', 'getMyDp');
});


Route::controller(LogoutController::class)
->middleware(['api', 'return-json']) // Use our JSON Middleware
->group(function () {
    Route::post('logout', 'logout');
});

Route::controller(RefreshController::class)
->middleware(['api', 'return-json']) // Use our JSON Middleware
->group(function () {
    Route::post('refresh', 'refresh');
});


Route::controller(TodoController::class)
->middleware(['api', 'return-json']) // Use our JSON Middleware
->group(function () {
    Route::get('items', 'index');
    Route::post('add', 'create_item');
    Route::get('item/{id}', 'show');
    Route::put('update/{id}', 'update');
    Route::delete('delete/{id}', 'destroy');

    Route::get('user', 'get_user');
    Route::get('myitems', 'get_my_todos');
    //axilliary
    Route::get('useritems/{id}', 'get_specific_user_todos');


    //image display route
    Route::get('image/{filename}', 'displayImage')->name('image.displayImage');

    Route::get('getusers', 'get_all_users_admin');
    Route::get('getitems', 'get_item_count');
    Route::get('getall', 'get_all_users_and_their_items');


    //user crud functions
    Route::put('updatemyprofile', 'edit_user');
});
