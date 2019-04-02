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

});
Route::get('/article','ArticleController@test');

/**微信登录**/
Route::get('wx/auth/login','WxController@login');
/**微信回调**/
Route::any('wx/auth/redirect','WxController@redirect');
Route::group(['middleware' => 'refresh.token'] ,function() {
    /**应收附件预览**/
    Route::get('view/attr', 'ARSumAttrViewController@index');
    /**附件下载**/
    Route::get('file/download/{key}', 'AttachmentController@download');
});


