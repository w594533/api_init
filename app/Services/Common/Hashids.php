<?php
namespace App\Services\Common;

use App\Exceptions\Auth\HashNotDecodeException;

class Hashids{

    /**
     * 加密
     */
    public static function encode($value)
    {
        $hanle = new \Hashids\Hashids(self::solt());
        return $hanle->encode($value);
    }

    /**
     * 解密
     */
    public static function decode($value)
    {
        $hanle = new \Hashids\Hashids(self::solt());
        $data = $hanle->decode($value);
        if(count($data) <= 0)
        {
            throw new HashNotDecodeException("id不合法");
        }else{
            return $data[0];
        }
    }

    private static function solt()
    {
        return env('HUANJING', 'prod');
    }

}