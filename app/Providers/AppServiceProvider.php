<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Yansongda\Pay\Pay;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 往服务容器中注入一个名为 alipay 的单例对象
        $this->app->singleton('alipay', function () {
            $config = config('pay.alipay');
            $config['notify_url'] = route('payment.alipay.notify');
            // $config['return_url'] = route('payment.alipay.return');
            // 判断当前项目运行环境是否为线上环境
            if (app()->environment() !== 'production') {
                $config['mode']         = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::DEBUG;
                // $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个支付宝支付对象
            return Pay::alipay($config);
        });

        $this->app->singleton('wechat_pay', function() {
            $config = config('pay.wechat');
            if (app()->environment() !== 'production') {
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个微信支付对象
            return Pay::wechat($config);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend('mobile', function ($attribute, $value, $parameters) {
            $phoneHead = array("128", "134", "135", "136", "137", "138", "139", "147",
                "150", "151", "152", "157", "158", "159", "182", "183", "184", "187", "188",
                "130", "131", "132", "145", "155", "156", "173", "175", "176", "185", "186",
                "133", "153", "180", "181", "189", "170", "171", "177", "178", '199', '198',
                '166');

            if(strlen($value) == 11 && is_numeric($value)) {
                $head = substr($value,0,3);
                foreach($phoneHead as $getHead) {
                    if($getHead==$head) {
                        return true;
                    }
                }
            }
            return false;
        });

        \DB::listen(function ($query) {
            // $tmp = str_replace('?', '"'.'%s'.'"', $query->sql);
            // $qBindings = [];
            // foreach ($query->bindings as $key => $value) {
            //     if (is_numeric($key)) {
            //         $qBindings[] = $value;
            //     } else {
            //         $tmp = str_replace(':'.$key, '"'.$value.'"', $tmp);
            //     }
            // }
            // $tmp = vsprintf($tmp, $qBindings);
            // $tmp = str_replace("\\", "", $tmp);
            // \Log::info(' execution time: '.$query->time.'ms; '.$tmp."\n\n\t");

        });
    }
}
