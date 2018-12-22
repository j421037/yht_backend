<?php
namespace Miao\Providers\Wechat;

use App\User;
use Exception;

class Wechat implements WechatInterface 
{
	protected $corpId;
	protected $agentid;
	protected $corpSecret;
	protected $contactSecret;

	public function __construct(string $corpId, string $corpSecret, string $contactSecret, string $agentid)
	{
		$this->corpId = $corpId;
		$this->agentId = $agentid;
		$this->corpSecret = $corpSecret;
		$this->contactSecret = $contactSecret;
	}

	use AccessToken;

	/**
	* 企业号发送应用消息
	*/
	public function sendApplicationMessage(string $name)
	{
		$params = [
			"touser" 	=> "@all",
			"msgtype"	=> "text",
			"agentid"	=> $this->agentId,
			"text"		=> [
				"content" => "有新的客户资源发布,<a href='http://e.yhtjc.com/v2/public/index.php/wx/auth/login'>点击查看</a>\n\n".date('Y-m-d H:i:s', time())
			],
			"safe" => 0
		];

		$url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$this->initToken();

		return $this->CURL($url, $params);
		// var_dump(json_encode($params));
	}
	
	/**
	* 企业号创建一个用户
	*/
	public function create($user)
	{
		$url = "https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token=".$this->initToken(2);

		return $this->CURL($url, $user);
	}

	/**
	* 创建一个群聊
	*/
	public function organizeChat(string $name, array $userList, string $owner) 
	{
		$url = "https://qyapi.weixin.qq.com/cgi-bin/appchat/create?access_token=".$this->initToken();

		$params = [
			"name" 		=> $name,
			"owner"	 	=> $owner,
			'userlist'	=>  $userList
		];
		
		return $this->CURL($url, $params);	
	}

	/**
	*获取所有成员信息
	* @param $departmentId
	*/
	public function simplelist($departmentId = 1)
	{
		$url = 'https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token='.$this->initToken().'&department_id='.$departmentId.'&fetch_child=1';

		$result = $this->CURL($url);

		if ($result['errmsg'] == 'ok') {
			return $result['userlist'];
		}

		return false;
	}

	/**
	* 发送群聊消息
	*/
	public function sendChatCardMessage(string $chatId,string $title, string $message, string $url)
	{
		$api = "https://qyapi.weixin.qq.com/cgi-bin/appchat/send?access_token=".$this->initToken();

		$params = [
			'chatid' 	=> $chatId,
			'msgtype'	=> 'textcard',
			'textcard'	=> [
				'title'		=> $title,
				// 'description' => "<div class=\"gray\">".date('Y-m-d H:i:s')."</div><div class=\"highlight\">有新的客户资源发布</div>",
				'description'	=> $message,
				'url'		  	=> $url,
        		"btntxt"		=>"点击查看"
			]
		];

		
		return $this->CURL($api, $params);
	}

	/**
	* 群聊新增用户
	*/
	public function ChatAddUser(string $chatId, array $userList)
	{
		$api = "https://qyapi.weixin.qq.com/cgi-bin/appchat/update?access_token=".$this->initToken();

		$params = [
			'chatid' 	=> $chatId,
			'add_user_list' => $userList
		];
		
		return $this->CURL($api, $params);
	}

	/**
	* 获取code
	*/
	public function getCode($redirect)
	{
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid   =".$this->corpId."&redirect_uri=".$redirect."&response_type=code&scope=SCOPE&agentid=".$this->agentId."&state=STATE#wechat_redirect";

		header("Location: {$url}");
	}

	/**
	* 根据code和access token 获取成员信息
	*/
	public function getUserInfo(string $code)
	{
		$url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=".$this->initToken()."&code=".$code;

		return $this->CURL($url);
	}
}