<?php

namespace App\Services;

use App\Exceptions\InvalidRequestException;
use JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Cache;

class OauthService extends BaseService
{

    /**
     * 账号密码登录
     */
    public function login($request)
    {
        $user = User::where('account', $request->account)->first();
        if (!$user) {
            throw new InvalidRequestException('账号或者密码错误');
        }
        // $pwd = Hash::make($request->password);
        if (!Hash::check($request->password, $user->password)) {
            throw new InvalidRequestException('账号或者密码错误');
        }
        return $this->loginFromUser($user);
    }

    public function register($request)
    {
        if (User::where('account', $request->account)->exists()) {
            throw new InvalidRequestException('手机号码已经存在');
        }

        $this->storeUser([
            'account' => $request->account,
            'password' => $request->password,
            'name' => $request->name
        ]);
    }

    /**
     * jwt 登录
     */
    public function loginFromUser($user)
    {
        $token = Auth::guard('api')->fromUser($user);
        $result = [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
        if ($user->openid) {
            $result['openid'] = $user->openid;
        }
        return $result;
    }

    public function storeUser($user)
    {
        $user = new User([
            'account' => isset($user['account']) ? $user['account'] : '',
            'password' => isset($user['password']) ? Hash::make($user['password']) : '',
            'openid' => isset($user['openid']) ? $user['openid'] : '',
            'nickname' => isset($user['nickname']) ? $user['nickname'] : '',
            'name' => isset($user['name']) ? $user['name'] : '',
            'avatar' => isset($user['avatar']) ? $user['avatar'] : '',
            'sex' => isset($user['sex']) ? $user['sex'] : 0,
        ]);
        $user->save();
        return $user;
    }

    /**
     * 更新用户信息
     */
    public function updateUser($user, $user_info)
    {
        $user->update($user_info);
        return $user;
    }

    /**
     * 微信授权跳转链接
     */
    public function wechatOauthRedirectUrl($request)
    {
        if (!$request->has('redirect_url') || !$request->redirect_url) {
            throw new InvalidRequestException('参数跳转链接错误');
        }
        return ['url' => (new WechatService())->getOauthRedirectUrl($request->redirect_url)];
    }

    /**
     * 微信授权登录
     */
    public function wechatOauth($request)
    {
        if (!$request->has('code') || !$request->code) {
            throw new InvalidRequestException('code参数错误');
        }
        $user = (new WechatService())->oauthByCode($request->code);
        $original = $user->getOriginal();

        if (!User::where('openid', $user->getId())->exists()) {
            $user_info = [
                'openid' => $user->getId(),
                'name' => $user->getName(),
                'nickname' => $user->getNickname(),
                'avatar' => $user->getAvatar(),
                'sex' => $original['sex'],
            ];
            
            $user = $this->storeUser($user_info);
        } else {
            $user = User::findByOpenid($user->getId());
        }
        \Log::debug('user', [$user]);
        return $this->loginFromUser($user);
    }

    public function logout()
    {
        auth('api')->logout();
    }
    
}
