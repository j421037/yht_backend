<?php

/**
* 企业微信管理
*2018 07 25
*/

namespace App\Http\Controllers;

use Miao;
use App\User;
use App\BaseData;
use App\Http\Requests\ChatFormRequest;
use Illuminate\Http\Request;

class WechatController extends Controller
{
	/**
	* 新建群聊
	*@param $chatName
	*@param $userlist
	*@return response
	*/
    public function storeChat(ChatFormRequest $request)
    {
    	$userlist = json_decode(json_encode(User::find($request->userlist)->pluck('phone')),true);
    	$owner = User::find($request->owner)->phone;

    	$result = Miao::createChat($request->name, $userlist, $owner);

    	if ($result['errcode'] == 0 && $result['errmsg'] == 'ok') {

    		BaseData::create(['value' => $result['chatid'], 'description' => $request->name, 'type' => 'wechat', 'name' => 'chatTest']);
    		return response(['status' => 'success'], 200);
    	}

    	return response(['status' => 'error', 'error' => '新建失败'], 200);
    }
}
