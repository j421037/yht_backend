<?php
/**
* 微信接口类
*/
namespace Miao\Providers\Wechat;

interface WechatInterface {

	/**
	* 通过微信发送应用消息
	* @param $name 客户名称
	* @return bool 发布结果
	*/

	public function sendApplicationMessage(string $name);
	// public function organizeChat(string $name, array $userList, string $owner);
	// public function sendChatMessage(string $chatId,string $message);
	public function sendChatCardMessage(string $chatId,string $title, string $message, string $url);
	public function ChatAddUser(string $chatId, array $userList);
	public function create($user);

}

