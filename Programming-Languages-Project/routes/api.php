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

Route::post('/register'                                ,'API\AuthApiController@register');
Route::post('/login'                                   ,'API\AuthApiController@login');
Route::post('/logout'                                  ,'API\AuthApiController@logout');

Route::post('/addItem'                                 ,'API\ItemController@addItem');
// Route::post('/addDiscount'                             ,'API\ItemController@add_discount');
Route::post('updateitem/{id}'                          ,'API\ItemController@updateitem');
Route::get('deleteItem/{id}'                           ,'API\ItemController@deleteItem');
Route::get('itemDetails/{id}'                          ,'API\ItemController@itemDetails');

Route::get('items'                                     ,'API\ItemController@items');
Route::get('search/{name}'                             ,'API\ItemController@search');
Route::get('item/{id}'                                 ,'API\ItemController@itemId');
Route::get('addRemoveInteract/{user_id}/{item_id}'     ,'API\ItemController@addRemoveInteract');
Route::post('addComment/{user_id}/{item_id}'           ,'API\ItemController@addComment');
Route::get('removeComment/{comm_id}'                   ,'API\ItemController@removeComment');
Route::get('Sort'                                      ,'API\ItemController@Sort');






