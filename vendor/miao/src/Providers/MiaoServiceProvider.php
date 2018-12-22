<?php
namespace Miao\Providers;

use Miao\Miao;
use Miao\Providers\Wechat\Wechat;
use Illuminate\Support\ServiceProvider;

class MiaoServiceProvider extends ServiceProvider
{
    // protected $defer = false;
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        
        $this->publishes([__DIR__.'/../config/config.php'=> config_path('miao.php')], 'config');
        
        $this->bootBindings();
    }

    public function bootBindings()
    {
        
        $this->app->singleton('\Miao\Providers\Wechat\WechatInterface', function ($app) {
           
            return $app['miao.provider.wechat'];
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerWechat();
        $this->registerMiao();
        
    }

    public function registerMiao()
    {
        
        $this->app->singleton('Miao', function ($app) {

            return new Miao($app['miao.provider.wechat']);
        });
    }

    public function registerWechat()
    {
        $this->app->singleton('miao.provider.wechat', function($app) {
            // return $this->getConfigInstance('Miao\Providers\Wechat\Wechat');

            return new Wechat($this->config($this->config('type').'.corpid'), 
                $this->config($this->config('type').'.corpsecret'), 
                $this->config($this->config('type').'.contactSecret'), 
                $this->config($this->config('type').'.agentid')
            );
        });
    }

    /**
     * Helper to get the config values.
     *
     * @param  string $key
     * @return string
     */
    protected function config($key, $default = null)
    {
        return config("miao.$key", $default);
    }

    /**
     * Get an instantiable configuration instance. Pinched from dingo/api :).
     *
     * @param  mixed  $instance
     * @return object
     */
    protected function getConfigInstance($instance)
    {
        if (is_callable($instance)) {
            return call_user_func($instance, $this->app);
        } elseif (is_string($instance)) {
            return $this->app->make($instance);
        }

        return $instance;
    }
}