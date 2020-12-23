<?php

namespace App\Services;

use App\Exceptions\InvalidRequestException;
use Overtrue\LaravelWeChat\Events\WeChatUserAuthorized;
use App\Models\User;
use Cache;
use Validator;
use Admin;

class WechatService extends BaseService
{
    private $oauth2_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?';

    public function snsapi_userinfo()
    {
        $user = session('wechat.oauth_user.default'); // 拿到授权用户资料
        \Log::debug('snsapi_userinfo', [$user]);
        return $user;

        //注意换成数组格式
        // $user 可以用的方法:
        // $user->getId();  // 对应微信的 OPENID
        // $user->getNickname(); // 对应微信的 nickname
        // $user->getName(); // 对应微信的 nickname
        // $user->getAvatar(); // 头像网址
        // $user->getOriginal(); // 原始API返回的结果
        // $user->getToken(); // access_token， 比如用于地址共享时使用
    }

    public function getOpenId()
    {
        $user = $this->snsapi_userinfo();
        $userId =  $user && $user->getId() ? $user->getId() : '';
        // \Log::info('user id openid:'. $userId);
        return $userId;
    }

    public function oauthByCode($request)
    {
        if (!$request->has('code') || !$request->code) {
            throw new InvalidRequestException('code参数错误');
        }
        $code = $request->code;

        $app = app('wechat.official_account');
        //跟进code获取token
        $token = $app->oauth->scopes(['snsapi_userinfo'])->getAccessToken($code);
        if (is_null($token) && $this->hasInvalidState()) {
            throw new InvalidRequestException('wechat token错误');
        }

        \Log::debug('token', [$token]);
        $user = $app->oauth->scopes(['snsapi_userinfo'])->user($token);
        \Log::debug('user', [$user]);
        return [
            'openid' => $user->getId()
        ];
    }

    /**
     * @param redirect_url 需要跳转的链接，就是带有code的链接
     * 获取拼接后的授权链接
     */
    public function getOauthRedirectUrl($request)
    {
        if (!$request->has('redirect_url') || !$request->redirect_url) {
            throw new InvalidRequestException('参数跳转链接错误');
        }
        $app = app('wechat.official_account');
        // \Log::info($app->oauth->buildAuthUrlFromBase($request->redirect_url, 'snsapi_userinfo'));
        $redirectUrl = $this->oauth2_url . 'appid='.config('wechat.official_account.default.app_id').'&redirect_uri='.$request->redirect_url.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
        
        // $redirectUrl = $app->oauth->scopes(['snsapi_userinfo'])->redirect($request->redirect_url);
        \Log::debug('$redirectUrl', [$redirectUrl]);
        // header("Location: {$redirectUrl}");
        return ['url' => $redirectUrl];
    }
}
