<?php

namespace App\Http\Controllers\Api;

use Log;
use Illuminate\Http\Request;
use App\Http\Requests\OauthRequest;
use App\Services\OauthService;
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

    /**
     * 登录
     */
    public function login(OauthRequest $request, OauthService $service)
    {
        $result = $service->login($request);
        return $this->success($result);
    }

    public function register(OauthRequest $request, OauthService $service)
    {
        $result = $service->register($request);
        return $this->message('注册成功');
    }

    /**
     * 获取授权跳转链接
     */
    public function wechat_oauth_redirect_url(Request $request, OauthService $service)
    {
        $result = $service->wechatOauthRedirectUrl($request);
        return $this->success($result);
    }

    /**
     * 根据传递code获取，openid,以及微信用户信息
     */
    public function wechat_oauth(Request $request, OauthService $service)
    {
        $result = $service->wechatOauth($request);
        return $this->success($result);
    }

    public function logout(OauthService $service)
    {
        $service->logout();
        return $this->message('退出成功');
    }
}