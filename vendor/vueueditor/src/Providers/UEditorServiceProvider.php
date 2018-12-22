<?php
namespace VueUEditor\Providers;

use VueUEditor\UEditor;
use Illuminate\Support\ServiceProvider;

class UEditorServiceProvider extends ServiceProvider
{
	public function boot()
	{
		// $this->bootBindings();
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('editor.php'),
        ], 'config');

        // $this->bootBindings();
	}

	// public function bootBindings()
	// {
	// 	$this->app->singleton('UEditor', function ($app) {

 //            return new UEditor;
 //        });
	// }

	/**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('UEditor', function ($app) {
            
            $string = config("UEditor.conf");
            $config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", $string), true);
            return new UEditor($config);
        });
        
    }

}
