<?php
namespace VueUEditor\Facades;
use Illuminate\Support\Facades\Facade;

class VueUEditorFacade extends Facade 
{
	protected static function getFacadeAccessor()
	{
		return 'UEditor';
	}
}