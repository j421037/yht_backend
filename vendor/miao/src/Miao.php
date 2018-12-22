<?php
namespace Miao;

use Miao\Providers\Wechat\WechatInterface;

Class Miao
{
	protected $wechat;

	public function __construct(WechatInterface $WechatInterface)
	{
		$this->wechat = $WechatInterface;
	}

	public function say() 
	{
		// return 'hello';
		// var_dump($this->wechat->initToken());
		var_dump($this->wechat->sendMessage('hello world'));
	}

	/**
	* 在企业号中创建一个用户
	*@param $user  [name, userid, department]
 	*/
	public function createUser($phone, $name, array $department = [1] )
	{

		$user = ['userid' => $phone,'mobile' => $phone, 'name' => $name, 'department' => $department];

		return  $this->wechat->create($user);
	}

	public function simplelist($departmentId = 1) 
	{
		return $this->wechat->simplelist($departmentId);
	}

	/**
	* 新建一个群聊
	*/
	public function createChat($chatName, $userList, $owner)
	{
		return $this->wechat->organizeChat($chatName, $userList, $owner);
	}

	/**
	* 发送一个卡片式群聊消息
	*"textcard":{
        "title" : "领奖通知",
        "description" : "<div class=\"gray\">2016年9月26日</div> <div class=\"normal\"> 恭喜你抽中iPhone 7一台，领奖码:520258</div><div class=\"highlight\">请于2016年10月10日前联系行 政同事领取</div>",
        "url":"https://zhidao.baidu.com/question/2073647112026042748.html",
        "btntxt":"更多"
    },
	*/
	public function sendChatCardMessage(string $chatId,string $title, string $message, string $url)
	{
		return $this->wechat->sendChatCardMessage($chatId, $title, $message, $url);
	}

	/**
	* 群聊新增成员
	*/
	public function ChatAddUser($chatId, array $userList)
	{
		return $this->wechat->ChatAddUser($chatId, $userList);
	}

	/**
	* 微信授权登录流程
	*/
	public function WxLogin(string $redirect)
	{
		$this->wechat->getCode($redirect);
	}

	/**
	* 获取微信企业号成员信息
	*/
	public function WxEnterpriseUserId($code)
	{
		return $this->wechat->getUserInfo($code);
	}
}