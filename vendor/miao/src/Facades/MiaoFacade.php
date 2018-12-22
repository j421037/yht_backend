<?php
namespace Miao\Facades;
use Illuminate\Support\Facades\Facade;

class MiaoFacade extends Facade 
{
	protected static function getFacadeAccessor()
	{

		return 'Miao';
	}
}