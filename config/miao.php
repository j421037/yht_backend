<?php
/**
* 微信基础配置
*/

return [

	/**
	* 微信公众号的类型分为服务号、 企业号
	*/

	'type' => 'work',


	/**
	* work 对应 企业号
	* 新源泰
	*/
	// 'work_xyt' => [
	// 	'corpid'		=> 'wwb0a787d5b8fe20ab', //新源泰企业id
	// 	'agentid'		=> '1000003',
	// 	'corpsecret'	=> 'Fcq5EfnELID_vd9CKpvOX8UibXscBmCtqhlrJYNHMKk',
	// 	'contactSecret'	=> 'XWWAiTosH_UPkmnf1xTnZ_b7vxGBnRFazO-M2JfjTCc'
	// ],
	/**
	* 宇宏泰
	*/
	'work'	=> [

		'corpid' 		=> 'ww9b0aad574dc2509b', //宇宏泰企业ID
		'agentid'		=> '1000002', //宇宏泰应用id
		'corpsecret'	=> '_lwKcIHHWvs8zUa-vDUq1dtZ3ZQZiM2PmgJFxN-1U4g', //宇宏泰应用的凭证密钥
		'contactSecret'	=> 'IgU6B0bMlVlznKo0ac12RXrifCvkhXAeQHI08YuQ3BU', //宇宏泰通讯录同步助手的秘钥，企业成员增删改查时需要用到
	]
	
];