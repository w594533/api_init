<?php

namespace App\Services;

use App\Models\User;
use Cache;
use Validator;
use Admin;

class WechatService extends BaseService
{
    public function snsapi_userinfo()
    {
        $user = session('wechat.oauth_user.default'); // 拿到授权用户资料
        // \Log::debug('snsapi_userinfo', [$user]);
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
}
