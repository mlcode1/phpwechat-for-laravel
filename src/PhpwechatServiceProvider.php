<?php

namespace Marin\Phpwechat;

use Illuminate\Support\ServiceProvider;

class PhpwechatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'Phpwechat'); // 视图目录指定
        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/vendor/phpwechat'),  // 发布视图目录到resources 下
            __DIR__.'/config/phpwechat.php' => config_path('phpwechat.php'), // 发布配置文件到 laravel 的config 下
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // 单例绑定服务
        $this->app->singleton('phpwechat', function ($app) {
            return new PhpWechat($app['session'], $app['config']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        // 因为延迟加载 所以要定义 provides 函数 具体参考laravel 文档
        return ['phpwechat'];
    }
}
