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
//    Header("Location:http://{$_SERVER['HTTP_HOST']}/");
    //dd($_SERVER);
    //return 66;
    header('Location:http://i.yhtjc.com/');
    exit;
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

Route::get("/aes", function() {
    $key = "0CoJUm6Qyw8W8jud";
    $iv = "0102030405060708";
    $data = '{"ids":"[484730184]","br":128000,"csrf_token":""}';
    $en_data = base64_encode(openssl_encrypt($data, "aes-256-cbc", $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv));
    var_dump($en_data);
//    $de_data = openssl_decrypt(base64_decode($en_data), "aes-256-cbc", $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
//    var_dump($de_data);

});

