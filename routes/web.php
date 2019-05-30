<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use FarhanWazir\GoogleMaps\GMaps;

Route::get('/','MapController@index')->name('map');
Route::get('/map','MapController@index')->name('map');
Route::post('/sort','MapController@sortMessages')->name('sort');
