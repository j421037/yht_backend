<?php
namespace VueUEditor;

class UEditor {
	public $config;
	public function __construct($config) {
		$this->config = $config;
	}
	public function Config()
	{
		return $this->config;
	}
}

