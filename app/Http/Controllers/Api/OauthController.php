<?php

namespace App\Http\Controllers\Api;

use Log;
use Illuminate\Http\Request;
use App\Services\WechatService;

class OauthController extends Controller
{

    /**
     * 处理微信的请求消息
     * 更多处理方式见：https://github.com/overtrue/laravel-wechat
     *
     * @return string
     */
    public function serve()
    {
        Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        $app = app('wechat.official_account');
        $app->server->push(function($message){
            return "欢迎关注";
        });

        return $app->server->serve();
    }

    public function get_oauth_redirect_url(Request $request, WechatService $service)
    {
        $result = $service->getOauthRedirectUrl($request);
        return $this->success($result);
    }

    public function oauth(Request $request, WechatService $service)
    {
        $result = $service->oauthByCode($request);
        return $this->success($result);
    }
}