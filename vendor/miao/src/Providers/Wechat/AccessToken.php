<?php
namespace Miao\Providers\Wechat;

use Miao\Providers\Model\WxToken;

trait AccessToken
{
	public $url = '';

	/**
	* @param $id  1 => 应用的secret  2 => 通讯录的的secret
	*/
	public function initToken($id = 1)
	{

		$secret = $this->corpSecret; 

		if ($id == 2) {

			$secret = $this->contactSecret;
		}

		$this->url = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid='.$this->corpId.'&corpsecret='.$secret;

		$token = WxToken::find($id);

		if (!$token || $token->expire < time()) {

			$result = $this->CURL($this->url);


			if ($result['errcode'] == 0 && $result['errmsg'] == 'ok') {

				$token = WxToken::updateOrCreate(['id' => $id],['token' => $result['access_token'], 'expire' => $result['expires_in'] + time()]);
				
			}
		}

		return $token->token;
	}

	public function CURL($url, $params = null) 
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);

		if (is_array($params) && !Empty($params)) {
			curl_setopt($ch,CURLOPT_POST,1);//设置请求方式  POST
			curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($params));//POST 数据
		}

		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);//false 规避SSL证书
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//false 规避SSL证书

		$result = curl_exec($ch);

		return json_decode($result, true);
	}
}