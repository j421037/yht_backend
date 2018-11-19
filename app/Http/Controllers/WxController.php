<?php

namespace App\Http\Controllers;

use Miao;
use JWTAuth;
use JWTFactory;
use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class WxController extends Controller
{
    /**微信登录**/
    public function login(Request $request)
    {	

    	$redirect_uri = "https://e.yhtjc.com/v2/public/index.php/wx/auth/redirect";

    	Miao::WxLogin($redirect_uri);

    }

    /**回调**/
    public function redirect(Request $request)
    {
    	$result = $request->all('code');
         
    	$result = Miao::WxEnterpriseUserId($result['code']);
        // var_dump($result);die;
        //如果没有对应的用户信息  则需要注册一个新的用户
    	if (!isset($result['UserId']) || Empty($result['UserId'])) {
            //注册通道
    		// return view('error', ['status' => 403, 'content' => '你暂未获取权限，请联系管理员申请加入企业', 'img' => asset('storage/1.png')]);
            header("Location: https://e.yhtjc.com/v2/public/dist/#/signup"); 
            exit();
    	}

        //已经注册 则生成一个token 并且跳转到登录首页

        $user = User::where(['phone' => $result['UserId']])->first();

        $customClaims = ['sub' => $user->id];

        $payload = JWTFactory::make($customClaims);

        $token = JWTAuth::encode($payload)->get();

    	$url = "https://e.yhtjc.com/v2/public/dist/#/m/customer/newest?token=".$token;

        header("Location: {$url}");
        // echo '<pre>';
        // var_dump($url);
    }
}
