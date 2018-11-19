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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/article','ArticleController@test');

/**微信登录**/
Route::get('wx/auth/login','WxController@login');
/**微信回调**/
Route::any('wx/auth/redirect','WxController@redirect');


