<?php
use Carbon\Carbon;

function switchToBoolean($text)
{
    return $text === 'on' ? true : false;
}

function switchToText($boolean)
{
    return $boolean ? 'on':'off';
}

function randomInt($length = 4)
{
    $pattern = '123456789';
    $key = '';
    for ($i = 0; $i < $length; $i++) {
        $key .= $pattern{
            mt_rand(0, 8)};    //生成php随机数   
    }
    return $key;
}

function randomkeys($length)
{
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    $key = '';
    for ($i = 0; $i < $length; $i++) {
        $key .= $pattern{
            mt_rand(0, 35)};    //生成php随机数   
    }
    return $key;
}

function generateStuffNo($length = 8)
{
    $pattern = '123456789ABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    $key = '';
    for ($i = 0; $i < $length; $i++) {
        $key .= $pattern{
            mt_rand(0, 35)};    //生成php随机数   
    }
    return $key;
}

function file_local_path($file)
{
    return storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR) . $file;
}

function file_host_path($file)
{
    return config('app.url') . \Storage::url($file);
}

function route_name($route_name)
{
    return str_replace(".", '-', $route_name);
}

function isWeixin()
{
    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    } else {
        return false;
    }
}

/**
     * 时间转为分钟
     * @param time 时间(00:00:00)
     * @returns {string} 分钟（单位：分钟）
     */
function time_to_min ($time) {
    list($hour, $min, $sec) = explode(":", $time);
    $s = intval($hour*3600) + intval($min*60) + intval($sec);
    return intval($s / 60);
};

function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备
    if (isset($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * $seats [3_2,1_3]
 */
function seatToText($seats)
{
    if (is_array($seats)) {
        $result = [];
        foreach($seats as $seat) {
            list($row, $col) = explode("_", $seat);
            $result[] = $row.'排'.$col.'座';
        }
    } else {
        list($row, $col) = explode("_", $seats);
        return $row.'排'.$col.'座';
    }
    
    return $result;
}

function parseTimeDateToHan($datetime) {
    $unix = strtotime($datetime);
    return date("Y", $unix).'年'.date("m", $unix).'月'.date('d', $unix).'日';
}

function parseTimeTimeToHan($datetime) {
    $unix = strtotime($datetime);
    return date("H:i", $unix);
}

function parseDateTimeToZh($datetime) {
    $tomorrow = Carbon::tomorrow()->toDateString();

    $after_tomorrow = date("Y-m-d", strtotime(Carbon::tomorrow()->addDay()));

    if ($datetime >= now()->toDateString() . ' 00:00:00' && $datetime <= now()->toDateString() . ' 23:59:59') {
        return '今天'.date("m月d日", strtotime($datetime));
    }

    if ($datetime >= $tomorrow . ' 00:00:00' && $datetime <= $tomorrow . ' 23:59:59') {
        return '明天'.date("m月d日", strtotime($datetime));
    }

    if ($datetime >= $after_tomorrow . ' 00:00:00' && $datetime <= $after_tomorrow . ' 23:59:59') {
        return '后天'.date("m月d日", strtotime($datetime));
    }

    return date("m月d日", strtotime($datetime));
    
}